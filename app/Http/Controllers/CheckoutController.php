<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderTracking;
use App\Models\Payment;
use App\Models\PrescriptionItem;
use App\Models\User;
use App\Services\CartService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CheckoutController extends Controller
{
    /**
     * No online gateway is integrated, so only methods that do not claim an
     * instant payment are offered. Payment stays "pending" until staff confirm it.
     */
    public const PAYMENT_METHODS = [
        'cod'           => 'Cash on delivery',
        'bank_transfer' => 'Bank transfer',
    ];

    public function __construct(private CartService $cart, private StockService $stock) {}

    public function index()
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $totals = $this->cart->totals();
        if ($totals['blocked']) {
            return redirect()->route('cart.index')->with('error', 'Please fix the highlighted items before checkout.');
        }

        return view('cart.checkout', $totals + [
            'paymentMethods' => self::PAYMENT_METHODS,
            'user'           => auth()->user(),
        ]);
    }

    public function placeOrder(Request $request)
    {
        $wantsAccount = ! auth()->check() && $request->boolean('create_account');

        $rules = [
            'customer_name'    => 'required|string|max:100',
            'customer_email'   => ['required', 'email', 'max:150'],
            'customer_phone'   => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'shipping_address' => 'required|string|max:500',
            'shipping_city'    => 'required|string|max:100',
            'shipping_area'    => 'nullable|string|max:100',
            'payment_method'   => 'required|in:' . implode(',', array_keys(self::PAYMENT_METHODS)),
            'customer_note'    => 'nullable|string|max:1000',
        ];
        if ($wantsAccount) {
            // Optional account: only validated when the box is ticked.
            $rules['customer_email'][] = 'unique:users,email';
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $data = $request->validate($rules, [
            'customer_email.unique' => 'This email already has an account. Please sign in, or untick "Create an account".',
        ]);
        $orderData = Arr::only($data, ['customer_name', 'customer_email', 'customer_phone', 'shipping_address',
            'shipping_city', 'shipping_area', 'payment_method', 'customer_note']);

        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index');
        }

        $newUser = null;

        try {
            $order = DB::transaction(function () use ($data, $orderData, $wantsAccount, &$newUser) {
                // Re-price everything from the database, inside the transaction.
                $totals = $this->cart->totals();
                if ($totals['blocked'] || $totals['lines']->isEmpty()) {
                    throw new RuntimeException('Some items in your cart are no longer available in the requested quantity.');
                }

                $userId = auth()->id();
                if ($wantsAccount) {
                    $newUser = User::create([
                        'name'              => $data['customer_name'],
                        'email'             => $data['customer_email'],
                        'phone'             => $data['customer_phone'],
                        'address'           => $data['shipping_address'],
                        'city'              => $data['shipping_city'],
                        'password'          => $data['password'],
                        'role'              => 'customer',
                        'is_active'         => true,
                        'email_verified_at' => null,   // not verified: we have not confirmed the email
                    ]);
                    $userId = $newUser->id;
                }

                $order = Order::create($orderData + [
                    'order_number'    => Order::generateOrderNumber(),
                    'user_id'         => $userId,
                    'subtotal'        => $totals['subtotal'],
                    'discount'        => $totals['discount'],
                    'shipping_charge' => $totals['shipping'],
                    'total_amount'    => $totals['total'],
                    'coupon_code'     => $totals['coupon']?->code,
                    'status'          => 'pending',
                    'payment_status'  => 'pending',
                ]);

                foreach ($totals['lines'] as $line) {
                    $product = $line->product;

                    // Locks the row, refuses to oversell and logs the movement.
                    $this->stock->adjust($product->id, -$line->quantity, 'sale', $order->order_number);

                    if ($product->requires_prescription) {
                        $this->consumePrescription($product->id, $line->quantity);
                    }

                    OrderItem::create([
                        'order_id'     => $order->id,
                        'product_id'   => $product->id,
                        'product_name' => $product->name,
                        'product_sku'  => $product->sku,
                        'quantity'     => $line->quantity,
                        'unit_price'   => $line->unit_price,
                        'total_price'  => $line->line_total,
                    ]);
                }

                Payment::create([
                    'order_id' => $order->id,
                    'amount'   => $totals['total'],
                    'method'   => $data['payment_method'],
                    'status'   => 'pending',
                ]);

                OrderTracking::create([
                    'order_id' => $order->id,
                    'status'   => 'pending',
                    'note'     => 'Order placed. Payment is pending.',
                ]);

                if ($totals['coupon']) {
                    // Atomic: only counts if the usage limit still allows it.
                    $claimed = Coupon::where('id', $totals['coupon']->id)
                        ->where(fn ($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'))
                        ->increment('used_count');
                    if (! $claimed) {
                        throw new RuntimeException('That coupon has just reached its usage limit. Please remove it and try again.');
                    }
                }

                return $order;
            });
        } catch (RuntimeException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }

        $this->cart->clear();
        session()->put('last_order', $order->order_number);

        // Sign the new customer in only after the order is safely saved.
        if ($newUser) {
            Auth::login($newUser);
            $request->session()->regenerate();
            session()->put('last_order', $order->order_number);
        }

        return redirect()->route('order.success', $order->order_number);
    }

    /** Use up the customer's verified prescription allowance (oldest first). */
    private function consumePrescription(int $productId, int $qty): void
    {
        $items = PrescriptionItem::where('product_id', $productId)
            ->where('is_verified', true)
            ->whereHas('prescription', fn ($q) => $q->where('customer_id', auth()->id())->where('status', 'verified'))
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($items as $item) {
            if ($qty <= 0) {
                break;
            }
            $use = min($qty, $item->remaining_qty);
            if ($use > 0) {
                $item->increment('fulfilled_qty', $use);
                $qty -= $use;
            }
        }

        if ($qty > 0) {
            throw new RuntimeException('Your prescription no longer covers the requested quantity.');
        }

        // Mark prescriptions with nothing left as fulfilled.
        $ids = $items->pluck('prescription_id')->unique();
        foreach ($ids as $pid) {
            $open = PrescriptionItem::where('prescription_id', $pid)->where('is_verified', true)
                ->whereColumn('fulfilled_qty', '<', 'approved_qty')->exists();
            if (! $open) {
                \App\Models\Prescription::where('id', $pid)->update(['status' => 'fulfilled']);
            }
        }
    }

    public function success($orderNumber)
    {
        // Only the buyer (or the same browser session) may see the confirmation.
        $order = Order::with('items')->where('order_number', $orderNumber)->firstOrFail();
        $allowed = session('last_order') === $order->order_number
            || (auth()->check() && (auth()->id() === $order->user_id || auth()->user()->isAdmin()));
        abort_unless($allowed, 404);

        return view('cart.order-success', compact('order'));
    }
}
