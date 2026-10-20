<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Prescription;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Collection;

/**
 * Session cart that stores only product ids and quantities.
 * Prices, stock, coupons and prescription limits are always re-read from the
 * database, so nothing a browser sends can change what a customer is charged.
 */
class CartService
{
    private const SESSION_KEY = 'cart';
    private const MAX_PER_LINE = 20;

    /** @return array<int,int> product_id => quantity */
    public function raw(): array
    {
        $out = [];
        foreach ((array) session(self::SESSION_KEY, []) as $id => $row) {
            $qty = is_array($row) ? (int) ($row['quantity'] ?? 0) : (int) $row;
            if ($qty > 0) {
                $out[(int) $id] = $qty;
            }
        }
        return $out;
    }

    private function save(array $cart): void
    {
        $store = [];
        foreach ($cart as $id => $qty) {
            $store[$id] = ['product_id' => $id, 'quantity' => $qty];
        }
        session([self::SESSION_KEY => $store]);
    }

    public function count(): int
    {
        return array_sum($this->raw());
    }

    public function isEmpty(): bool
    {
        return $this->raw() === [];
    }

    /**
     * Cart lines priced from the database.
     * Each line: product, quantity, unit_price, line_total, problem (null|string)
     */
    public function lines(): Collection
    {
        $cart = $this->raw();
        if (! $cart) {
            return collect();
        }

        $products = Product::with('category')->whereIn('id', array_keys($cart))->get()->keyBy('id');

        return collect($cart)->map(function ($qty, $id) use ($products) {
            $product = $products->get($id);
            if (! $product || ! $product->is_active) {
                return (object) ['product' => $product, 'quantity' => $qty, 'unit_price' => 0, 'line_total' => 0,
                    'problem' => 'This product is no longer available.', 'cart_id' => $id];
            }

            $problem = null;
            if ($product->stock < 1) {
                $problem = 'Out of stock.';
            } elseif ($qty > $product->stock) {
                $problem = "Only {$product->stock} in stock.";
            } elseif ($product->requires_prescription && $qty > $this->prescriptionAllowance($product)) {
                $problem = 'Needs a verified prescription for this quantity.';
            }

            $unit = (float) $product->final_price;
            return (object) ['product' => $product, 'quantity' => $qty, 'unit_price' => $unit,
                'line_total' => round($unit * $qty, 2), 'problem' => $problem, 'cart_id' => $id];
        })->values();
    }

    /** Units of a prescription-only product the signed-in customer may still buy. */
    public function prescriptionAllowance(Product $product): int
    {
        $user = auth()->user();
        if (! $user) {
            return 0;
        }

        return (int) Prescription::where('customer_id', $user->id)
            ->where('status', 'verified')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->with(['items' => fn ($q) => $q->where('product_id', $product->id)->where('is_verified', true)])
            ->get()
            ->flatMap->items
            ->sum(fn ($item) => $item->remaining_qty);
    }

    /** @return array{0:bool,1:string} */
    public function add(int $productId, int $qty = 1): array
    {
        $qty = max(1, min($qty, self::MAX_PER_LINE));
        $product = Product::find($productId);

        if (! $product || ! $product->is_active) {
            return [false, 'This product is not available.'];
        }

        $cart = $this->raw();
        $target = ($cart[$productId] ?? 0) + $qty;

        if ($product->stock < $target) {
            return [false, $product->stock > 0
                ? "Only {$product->stock} available in stock."
                : 'This product is out of stock.'];
        }

        if ($product->requires_prescription && $target > $this->prescriptionAllowance($product)) {
            return [false, auth()->check()
                ? 'This medicine needs a prescription verified by our pharmacist.'
                : 'Please sign in. This medicine needs a verified prescription.'];
        }

        $cart[$productId] = min($target, self::MAX_PER_LINE);
        $this->save($cart);

        return [true, 'Added to cart.'];
    }

    /** @return array{0:bool,1:string} */
    public function setQuantity(int $productId, int $qty): array
    {
        $cart = $this->raw();
        if (! isset($cart[$productId])) {
            return [false, 'Item is not in your cart.'];
        }

        $qty = max(1, min($qty, self::MAX_PER_LINE));
        $product = Product::find($productId);
        if (! $product || ! $product->is_active) {
            return [false, 'This product is not available.'];
        }
        if ($qty > $product->stock) {
            return [false, "Only {$product->stock} available in stock."];
        }
        if ($product->requires_prescription && $qty > $this->prescriptionAllowance($product)) {
            return [false, 'Quantity exceeds what your prescription allows.'];
        }

        $cart[$productId] = $qty;
        $this->save($cart);

        return [true, 'Cart updated.'];
    }

    public function remove(int $productId): void
    {
        $cart = $this->raw();
        unset($cart[$productId]);
        $this->save($cart);
    }

    public function clear(): void
    {
        session()->forget([self::SESSION_KEY, 'coupon']);
    }

    /** Put every verified, unfulfilled item of a prescription into the cart. */
    public function addPrescription(Prescription $prescription): array
    {
        $added = 0;
        $skipped = [];
        $cart = $this->raw();

        foreach ($prescription->items()->with('product')->where('is_verified', true)->get() as $item) {
            $product = $item->product;
            $remaining = $item->remaining_qty;
            if (! $product || ! $product->is_active || $remaining < 1) {
                $skipped[] = $item->written_name;
                continue;
            }
            $qty = min($remaining, $product->stock, self::MAX_PER_LINE);
            if ($qty < 1) {
                $skipped[] = $product->name . ' (out of stock)';
                continue;
            }
            $cart[$product->id] = max($cart[$product->id] ?? 0, $qty);
            $added++;
        }

        $this->save($cart);
        return ['added' => $added, 'skipped' => $skipped];
    }

    // ── Money ────────────────────────────────────────────────────────

    public function applyCoupon(string $code): array
    {
        $coupon = Coupon::where('code', strtoupper(trim($code)))->first();
        if (! $coupon || ! $coupon->isValid()) {
            return [false, 'This coupon is invalid or has expired.'];
        }

        $subtotal = (float) $this->lines()->sum('line_total');
        if ($subtotal < (float) $coupon->min_order) {
            return [false, 'Minimum order for this coupon is Rs. ' . number_format($coupon->min_order, 0) . '.'];
        }

        session(['coupon' => ['code' => $coupon->code]]);
        return [true, 'Coupon applied.'];
    }

    public function removeCoupon(): void
    {
        session()->forget('coupon');
    }

    public function activeCoupon(?float $subtotal = null): ?Coupon
    {
        $code = session('coupon.code');
        if (! $code) {
            return null;
        }
        $coupon = Coupon::where('code', $code)->first();
        if (! $coupon || ! $coupon->isValid()) {
            return null;
        }
        $subtotal ??= (float) $this->lines()->sum('line_total');
        return $subtotal >= (float) $coupon->min_order ? $coupon : null;
    }

    public function freeShippingMin(): float
    {
        $v = Setting::get('free_shipping_min');
        return is_numeric($v) ? (float) $v : 999.0;
    }

    public function flatShipping(): float
    {
        $v = Setting::get('shipping_charge');
        return $v === null || $v === '' ? 150.0 : (float) $v;
    }

    /** @return array{lines:Collection,subtotal:float,discount:float,shipping:float,total:float,coupon:?Coupon,blocked:bool} */
    public function totals(): array
    {
        $lines = $this->lines();
        $subtotal = round((float) $lines->sum('line_total'), 2);
        $coupon = $this->activeCoupon($subtotal);

        $discount = 0.0;
        if ($coupon) {
            $discount = $coupon->type === 'percentage'
                ? $subtotal * (float) $coupon->value / 100
                : (float) $coupon->value;
            $discount = round(min($discount, $subtotal), 2);
        }

        $after = $subtotal - $discount;
        $shipping = ($lines->isEmpty() || $after >= $this->freeShippingMin()) ? 0.0 : $this->flatShipping();

        return [
            'lines'    => $lines,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping' => $shipping,
            'total'    => round($after + $shipping, 2),
            'coupon'   => $coupon,
            'blocked'  => $lines->contains(fn ($l) => $l->problem !== null),
        ];
    }
}
