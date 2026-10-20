{{-- ========== RECEIPTS INDEX ========== --}}
@extends('admin.layouts.app')
@section('meta_title','Receipts')
@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Receipts</h1>
    </div>
</div>
<div class="admin-card">
    <div style="overflow-x:auto">
        <table class="admin-table">
            <thead><tr><th>Order #</th><th>Customer</th><th>Amount</th><th>Payment Method</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td><strong>#{{ $order->order_number }}</strong></td>
                    <td>{{ $order->customer_name }}</td>
                    <td><strong>Rs. {{ number_format($order->total_amount,0) }}</strong></td>
                    <td>{{ strtoupper(str_replace('_',' ',$order->payment_method)) }}</td>
                    <td>{{ $order->created_at->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('admin.receipts.show', $order->id) }}" target="_blank" class="btn-admin-view">
                            <i class="fas fa-print"></i> Print
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-4 text-muted">No receipts found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="admin-card-footer">{{ $orders->links() }}</div>
</div>
@endsection
