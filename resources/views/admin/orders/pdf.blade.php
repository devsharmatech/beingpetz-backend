<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Invoice</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
        }

        .section {
            margin-bottom: 15px;
        }

        .box {
            border: 1px solid #ddd;
            padding: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: #f5f5f5;
            padding: 8px;
            border: 1px solid #ddd;
        }

        table td {
            padding: 8px;
            border: 1px solid #ddd;
        }

        .text-right {
            text-align: right;
        }

        .total {
            font-weight: bold;
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <div class="header">
        <div class="title">Order Invoice</div>
        <div>Order #: {{ $order->order_number }}</div>
        <div>Date: {{ \Carbon\Carbon::parse($order->placed_at)->format('d M Y') }}</div>
    </div>

    <!-- CUSTOMER -->
    <div class="section box">
        <strong>Customer Details</strong><br>
        Name: {{ $order->user->name ?? '-' }}<br>
        Payment Method: {{ $order->payment_method }}<br>
        Payment Status: {{ ucfirst($order->payment_status) }}<br>
        Order Status: {{ ucfirst($order->order_status) }}
    </div>

    <!-- ADDRESS -->
    <div class="section box">
        <strong>Delivery Address</strong><br>
        {{ $order->address->full_address ?? '-' }}
    </div>

    <!-- ITEMS -->
    <div class="section">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>

                @php $subtotal = 0; @endphp

                @foreach($order->items as $index => $item)
                    @php
                        $total = $item->price * $item->quantity;
                        $subtotal += $total;
                    @endphp

                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>₹{{ number_format($item->price, 2) }}</td>
                        <td>₹{{ number_format($total, 2) }}</td>
                    </tr>
                @endforeach

            </tbody>
        </table>
    </div>

    <!-- TOTAL -->
    <div class="section">
        <table>
            <tr>
                <td class="text-right">Subtotal</td>
                <td class="text-right">₹{{ number_format($subtotal, 2) }}</td>
            </tr>

            <tr>
                <td class="text-right">Discount</td>
                <td class="text-right">₹{{ number_format($order->discount_amount, 2) }}</td>
            </tr>

            <tr>
                <td class="text-right total">Final Amount</td>
                <td class="text-right total">₹{{ number_format($order->final_amount, 2) }}</td>
            </tr>
        </table>
    </div>

    <!-- FOOTER -->
    <div class="section" style="text-align:center; margin-top:20px;">
        Thank you for your purchase!
    </div>

</body>
</html>