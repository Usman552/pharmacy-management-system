<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items']);

        if ($s = $request->search) {
            $query->where(function ($q) use ($s) {
                $q->where('order_number', 'like', "%$s%")
                  ->orWhere('customer_name', 'like', "%$s%")
                  ->orWhere('customer_email', 'like', "%$s%")
                  ->orWhere('customer_phone', 'like', "%$s%");
            });
        }
        if ($status = $request->status) $query->where('status', $status);
        if ($payment = $request->payment_status) $query->where('payment_status', $payment);
        if ($from = $request->from) $query->whereDate('created_at', '>=', $from);
        if ($to = $request->to)     $query->whereDate('created_at', '<=', $to);

        $orders = $query->latest()->paginate(15)->withQueryString();

        $counts = Order::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status');
        $stats = [
            'total'      => $counts->sum(),
            'pending'    => $counts['pending'] ?? 0,
            'processing' => $counts['processing'] ?? 0,
            'shipped'    => $counts['shipped'] ?? 0,
            'delivered'  => $counts['delivered'] ?? 0,
            'cancelled'  => $counts['cancelled'] ?? 0,
        ];

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    public function show(Order $order)
    {
        $order->load(['items.product', 'user', 'payment', 'tracking.updatedBy']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order, StockService $stock)
    {
        $data = $request->validate([
            'status'          => 'required|in:pending,processing,shipped,delivered,cancelled,refunded',
            'tracking_number' => 'nullable|string|max:100',
            'courier_name'    => 'nullable|string|max:100',
            'admin_note'      => 'nullable|string|max:1000',
            'tracking_note'   => 'nullable|string|max:500',
        ]);

        if (! $order->canMoveTo($data['status'])) {
            $msg = "An order that is {$order->status} cannot be changed to {$data['status']}.";
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        DB::transaction(function () use ($data, $order, $stock) {
            $order = Order::whereKey($order->id)->lockForUpdate()->first();
            $old = $order->status;

            $update = [
                'status'          => $data['status'],
                'tracking_number' => $data['tracking_number'] ?? $order->tracking_number,
                'courier_name'    => $data['courier_name'] ?? $order->courier_name,
                'admin_note'      => $data['admin_note'] ?? $order->admin_note,
            ];

            if ($data['status'] === 'shipped') {
                $update['shipped_at'] = now();
            }
            if ($data['status'] === 'delivered') {
                $update['delivered_at'] = now();
                // Cash on delivery is collected by the courier at the door.
                if ($order->payment_method === 'cod') {
                    $update['payment_status'] = 'paid';
                    $order->payment?->update(['status' => 'completed']);
                }
            }

            // Cancelled orders return their stock to the shelf.
            if ($data['status'] === 'cancelled') {
                foreach ($order->items()->whereNotNull('product_id')->get() as $item) {
                    $stock->adjust($item->product_id, $item->quantity, 'cancellation', $order->order_number);
                }
                if ($order->payment_status === 'pending') {
                    $order->payment?->update(['status' => 'failed']);
                    $update['payment_status'] = 'failed';
                }
            }
            if ($data['status'] === 'refunded') {
                $update['payment_status'] = 'refunded';
                $order->payment?->update(['status' => 'refunded']);
            }

            $order->update($update);

            OrderTracking::create([
                'order_id'   => $order->id,
                'status'     => $data['status'],
                'note'       => $data['tracking_note'] ?? "Status changed from $old to {$data['status']}",
                'updated_by' => auth()->id(),
            ]);
        });

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Order status updated']);
        }

        return redirect()->route('admin.orders.show', $order->id)
            ->with('success', 'Order status updated to ' . ucfirst($data['status']) . '.');
    }

    public function updatePayment(Request $request, Order $order)
    {
        $data = $request->validate(['payment_status' => 'required|in:pending,paid,failed,refunded']);

        if (in_array($order->status, ['cancelled', 'refunded'], true) && $data['payment_status'] === 'paid') {
            return back()->with('error', 'A cancelled or refunded order cannot be marked as paid.');
        }

        DB::transaction(function () use ($order, $data) {
            $order->update(['payment_status' => $data['payment_status']]);
            $order->payment?->update(['status' => match ($data['payment_status']) {
                'paid' => 'completed', 'pending' => 'pending', 'failed' => 'failed', 'refunded' => 'refunded',
            }]);
            OrderTracking::create([
                'order_id' => $order->id, 'status' => $order->status,
                'note' => 'Payment marked ' . $data['payment_status'] . ' by staff.', 'updated_by' => auth()->id(),
            ]);
        });

        return redirect()->route('admin.orders.show', $order->id)->with('success', 'Payment status updated.');
    }

    public function destroy(Order $order)
    {
        // Orders are financial records; only cancelled ones may be removed.
        if ($order->status !== 'cancelled') {
            return back()->with('error', 'Only cancelled orders can be deleted. Cancel the order first.');
        }
        $order->delete();
        return redirect()->route('admin.orders.index')->with('success', 'Order deleted.');
    }

    public function receipt(Order $order)
    {
        $order->load(['items.product', 'user']);
        return view('admin.orders.receipt', compact('order'));
    }
}
