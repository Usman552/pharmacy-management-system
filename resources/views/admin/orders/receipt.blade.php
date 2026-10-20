<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt — #{{ $order->order_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Courier New', monospace; font-size: 13px; color: #000; background: #fff; max-width: 380px; margin: 0 auto; padding: 20px; }
        .receipt-header { text-align: center; border-bottom: 2px dashed #000; padding-bottom: 16px; margin-bottom: 16px; }
        .pharmacy-name { font-size: 22px; font-weight: 900; letter-spacing: 2px; margin-bottom: 4px; }
        .pharmacy-sub { font-size: 11px; color: #555; margin-bottom: 2px; }
        .receipt-title { font-size: 16px; font-weight: 700; margin: 12px 0 4px; text-transform: uppercase; letter-spacing: 1px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 12px; }
        .divider { border: none; border-top: 1px dashed #000; margin: 10px 0; }
        .divider-solid { border: none; border-top: 2px solid #000; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th { text-align: left; font-weight: 700; padding: 4px 0; border-bottom: 1px solid #000; }
        th:last-child, td:last-child { text-align: right; }
        td { padding: 5px 0; vertical-align: top; }
        .total-row { font-weight: 700; font-size: 14px; }
        .grand-total { font-size: 18px; font-weight: 900; }
        .footer-msg { text-align: center; margin-top: 20px; font-size: 11px; color: #555; line-height: 1.7; }
        .barcode { text-align: center; font-size: 28px; letter-spacing: 6px; margin: 12px 0 4px; }
        .order-num { text-align: center; font-size: 11px; letter-spacing: 2px; }
        @media print {
            body { max-width: 100%; }
            .no-print { display: none !important; }
            @page { margin: 5mm; }
        }
    </style>
</head>
<body>

<!-- Print Button -->
<div class="no-print" style="text-align:right;margin-bottom:16px">
    <button onclick="window.print()" style="background:#f2971f;border:none;color:#fff;padding:8px 20px;border-radius:6px;font-size:13px;font-weight:700;cursor:pointer">
        🖨️ Print Receipt
    </button>
    <button onclick="window.close()" style="background:#eee;border:none;color:#333;padding:8px 16px;border-radius:6px;font-size:13px;font-weight:700;cursor:pointer;margin-left:8px">
        ✕ Close
    </button>
</div>

<!-- Header -->
<div class="receipt-header">
    <div class="pharmacy-name">💊 CITY DRUG</div>
    <div class="pharmacy-sub">Your Trusted Pharmacy</div>
    <div class="pharmacy-sub">Main Bazar, Multan, Pakistan</div>
    <div class="pharmacy-sub">Tel: +92 300 1234567</div>
    <div class="pharmacy-sub">info@citydrug.pk</div>
</div>

<!-- Receipt Title -->
<div class="receipt-title" style="text-align:center">Sales Receipt</div>

<!-- Order Info -->
<hr class="divider">
<div class="info-row"><span>Receipt #</span><strong>{{ $order->order_number }}</strong></div>
<div class="info-row"><span>Date</span><span>{{ $order->created_at->format('d/m/Y H:i') }}</span></div>
<div class="info-row"><span>Customer</span><span>{{ $order->customer_name }}</span></div>
<div class="info-row"><span>Phone</span><span>{{ $order->customer_phone }}</span></div>
<div class="info-row"><span>City</span><span>{{ $order->shipping_city }}</span></div>
<div class="info-row"><span>Payment</span><span>{{ strtoupper(str_replace('_', ' ', $order->payment_method)) }}</span></div>
<hr class="divider-solid">

<!-- Items -->
<table>
    <thead>
        <tr>
            <th style="width:50%">Item</th>
            <th style="text-align:center">Qty</th>
            <th style="text-align:right">Price</th>
            <th style="text-align:right">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($order->items as $item)
        <tr>
            <td>{{ $item->product_name }}</td>
            <td style="text-align:center">{{ $item->quantity }}</td>
            <td style="text-align:right">{{ number_format($item->unit_price, 0) }}</td>
            <td style="text-align:right"><strong>{{ number_format($item->total_price, 0) }}</strong></td>
        </tr>
        @endforeach
    </tbody>
</table>

<hr class="divider-solid">

<!-- Totals -->
<div class="info-row"><span>Subtotal</span><span>Rs. {{ number_format($order->subtotal, 0) }}</span></div>
@if($order->discount > 0)
<div class="info-row"><span>Discount @if($order->coupon_code)({{ $order->coupon_code }})@endif</span><span>- Rs. {{ number_format($order->discount, 0) }}</span></div>
@endif
<div class="info-row"><span>Delivery</span><span>{{ $order->shipping_charge > 0 ? 'Rs. '.number_format($order->shipping_charge,0) : 'FREE' }}</span></div>
<hr class="divider">
<div class="info-row grand-total">
    <span>TOTAL AMOUNT</span>
    <span>Rs. {{ number_format($order->total_amount, 0) }}</span>
</div>
<hr class="divider-solid">

<!-- Status -->
<div class="info-row" style="margin-top:8px">
    <span>Order Status</span>
    <strong style="text-transform:uppercase">{{ $order->status }}</strong>
</div>
<div class="info-row">
    <span>Payment Status</span>
    <strong style="text-transform:uppercase">{{ $order->payment_status }}</strong>
</div>

@if($order->tracking_number)
<hr class="divider">
<div class="info-row"><span>Tracking #</span><strong>{{ $order->tracking_number }}</strong></div>
@if($order->courier_name)<div class="info-row"><span>Courier</span><span>{{ $order->courier_name }}</span></div>@endif
@endif

<!-- Barcode-style -->
<hr class="divider">
<div class="barcode">||| {{ $order->order_number }} |||</div>

<!-- Footer -->
<div class="footer-msg">
    ✓ Genuine medicines guaranteed<br>
    ✓ 7-day return policy<br>
    Thank you for choosing City Drug!<br>
    <strong>citydrug.pk</strong>
</div>

<script>
    // Auto print on load (optional - comment out if not needed)
    // window.onload = function() { window.print(); }
</script>
</body>
</html>
