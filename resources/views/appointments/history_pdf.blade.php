<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment History Report</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px; 
            background: white;
            margin: 0; 
            padding: 15px; 
            color: #333;
            line-height: 1.3;
        }
        .page-container {
            background: white;
            padding: 20px;
            margin: 0 auto;
            max-width: 800px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px 0;
            background: #0ea5e9;
            color: white;
            border-radius: 5px;
        }
        .header-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 10px;
        }
        .header-logo {
            height: 30px;
            vertical-align: middle;
        }
        .header-title {
            font-size: 24px;
            font-weight: bold;
            color: white;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .shop-info { 
            color: #e0f2fe; 
            font-size: 13px; 
            margin: 3px 0;
        }
        .receipt-info { 
            margin: 15px 0; 
            font-size: 13px; 
            color: #333;
            background: #f0f9ff;
            border-radius: 5px;
            padding: 12px;
            border-left: 3px solid #0ea5e9;
        }
        .receipt-label { 
            color: #0ea5e9; 
            font-weight: bold;
            display: inline-block;
            width: 100px;
        }
        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px;
            border: 1px solid #bae6fd;
            font-size: 11px;
        }
        th, td { 
            border: 1px solid #bae6fd; 
            padding: 6px 8px; 
            text-align: left; 
            vertical-align: middle;
        }
        th { 
            background: #0ea5e9; 
            color: white; 
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        tr:nth-child(even) {
            background-color: #f0f9ff;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-confirmed { background: #dbeafe; color: #1e40af; }
        .status-completed { background: #dcfce7; color: #166534; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .payment-paid { background: #dcfce7; color: #166534; }
        .payment-unpaid { background: #fee2e2; color: #991b1b; }
        .payment-rejected { background: #fee2e2; color: #991b1b; }
        .totals-container { 
            margin-top: 15px; 
            padding: 12px; 
            background: #f0f9ff; 
            border-radius: 5px; 
            border: 1px solid #bae6fd;
        }
        .totals-row {
            display: table;
            width: 100%;
        }
        .totals-left { 
            display: table-cell;
            width: 50%;
        }
        .totals-right { 
            display: table-cell;
            width: 50%;
            text-align: right; 
        }
        .total-item { 
            margin-bottom: 5px; 
            font-size: 13px; 
            font-weight: bold;
        }
        .total-item.final { 
            font-weight: bold; 
            color: #0ea5e9; 
            font-size: 14px; 
        }
        .footer { 
            margin-top: 15px; 
            color: #64748b; 
            font-size: 11px; 
            text-align: left;
            background: #f0f9ff;
            border-radius: 5px;
            padding: 12px;
            border-left: 3px solid #0ea5e9;
        }
        .footer-title {
            font-weight: bold; 
            color: #0ea5e9; 
            margin-bottom: 5px;
            font-size: 12px;
            text-transform: uppercase;
        }
        .footer-content { 
            line-height: 1.4; 
            color: #64748b; 
        }
        .signature-block { 
            margin-top: 10px; 
            text-align: right; 
            position: absolute;
            bottom: 100px;
            right: 40px;
        }
        .signature-container {
            display: inline-block;
            text-align: center;
        }
        .signature-name { 
            font-family: 'Times New Roman', serif; 
            font-size: 13px; 
            color: #0ea5e9; 
            font-weight: bold; 
            margin: 0;
            text-align: center;
            position: relative;
            z-index: 2;
        }
        .signature-img { 
            height: 35px; 
            margin-top: 100px; 
            display: block; 
            margin-left: auto; 
            margin-right: auto;
            position: relative;
            z-index: 1;
        }
        .signature-label { 
            font-size: 11px; 
            color: #64748b; 
            margin-top: 6px;
            text-align: center;
        }
        .page-number {
            text-align: center;
            margin-top: 15px;
            color: #94a3b8;
            font-size: 11px;
            border-top: 1px solid #bae6fd;
            padding-top: 8px;
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
        }
        .amount-cell {
            font-weight: bold;
            color: #0ea5e9;
            font-size: 12px;
        }
        .id-cell {
            font-weight: bold;
            color: #0ea5e9;
            font-size: 11px;
        }
        .date-time-cell {
            line-height: 1.2;
        }
        .date-time-cell div {
            font-weight: bold;
            color: #0ea5e9;
            font-size: 11px;
        }
        .date-time-cell small {
            color: #64748b;
            font-size: 10px;
        }
        .service-cell {
            font-size: 11px;
        }
        .reference-cell {
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="page-container">
    <div class="header">
            <div class="header-content">
        <img src="{{ public_path('images/logo.png') }}" class="header-logo" alt="Logo">
                <div class="header-title">Auto Repair Shop</div>
            </div>
        <div class="shop-info">Surigao City, Surigao del Norte</div>
        <div class="shop-info">Purok 2, Brgy. Luna, Surigao City, 8400</div>
        <div class="shop-info">Contact: 0912-345-6789 | Email: autorepairshop@email.com</div>
    </div>
        
    <div class="receipt-info">
            <div><span class="receipt-label">Report Date:</span> {{ now()->format('F d, Y h:i A') }}</div>
            <div><span class="receipt-label">Customer:</span> {{ Auth::user()->name }}</div>
            <div><span class="receipt-label">Email:</span> {{ Auth::user()->email }}</div>
            <div><span class="receipt-label">Total Records:</span> {{ $payments->count() }}</div>
    </div>
        
    <table class="table">
        <thead>
            <tr>
                    <th>ID</th>
                    <th>Date & Time</th>
                    <th>Service</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Payment Status</th>
                <th>Reference</th>
            </tr>
        </thead>
        <tbody>
            @php
                $subtotal = 0;
                    $taxRate = 0.00;
            @endphp
            @foreach($payments as $payment)
            @php
                $qty = 1;
                $desc = $payment->service_type ?? ($payment->service->name ?? 'Service');
                $unitPrice = $payment->service && $payment->service->price ? $payment->service->price : ($payment->total ?? $payment->amount_paid ?? 0);
                $amount = $unitPrice * $qty;
                $subtotal += $amount;
                    
                    $appointmentDate = \Carbon\Carbon::parse($payment->appointment_date);
                    $appointmentTime = \Carbon\Carbon::parse($payment->appointment_time);
                    $combinedDateTime = $appointmentDate->setTimeFrom($appointmentTime);
            @endphp
            <tr>
                    <td class="id-cell">#{{ $payment->id }}</td>
                    <td class="date-time-cell">
                        <div>{{ $combinedDateTime->format('M d, Y') }}</div>
                        <small>{{ $combinedDateTime->format('h:i A') }}</small>
                    </td>
                    <td class="service-cell">{{ $desc }}</td>
                    <td class="amount-cell">PHP {{ number_format($amount, 2) }}</td>
                    <td>
                        <span class="status-badge status-{{ $payment->status }}">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </td>
                    <td>
                        <span class="status-badge payment-{{ $payment->payment_status }}">
                            {{ ucfirst($payment->payment_status) }}
                        </span>
                    </td>
                    <td class="reference-cell">{{ $payment->reference_number ?? '-' }}</td>
            </tr>
            @endforeach
            @php
                $tax = $subtotal * $taxRate;
                $total = $subtotal + $tax;
            @endphp
        </tbody>
    </table>
        
        <div class="totals-container">
            <div class="totals-row">
                <div class="totals-left">
                    <div class="total-item">
                        <strong>Subtotal:</strong> PHP {{ number_format($subtotal, 2) }}
                    </div>
                    @if($taxRate > 0)
                    <div class="total-item">
                        <strong>Sales Tax {{ $taxRate * 100 }}%:</strong> PHP {{ number_format($tax, 2) }}
                    </div>
                    @endif
                </div>
                <div class="totals-right">
                    <div class="total-item final">
                        <strong>Total Amount:</strong> PHP {{ number_format($total, 2) }}
                    </div>
                </div>
            </div>
        </div>

    <div class="footer">
            <div class="footer-title">Terms & Conditions</div>
            <div class="footer-content">
                • Payment is due within 15 days of receipt<br>
                • Please make checks payable to: Auto Repair Shop<br>
                • All services are guaranteed for 30 days<br>
                • Cancellation requires 24-hour notice<br>
                • Prices are subject to change without prior notice<br>
                • This report is generated on {{ now()->format('F d, Y \a\t h:i A') }}
            </div>
    </div>

    <div class="signature-block">
            <div class="signature-container">
        <img src="{{ public_path('images/signature.png') }}" class="signature-img" alt="Signature">
        <div class="signature-name">Clarence Angelo D. Lisondra</div>
        <div class="signature-label">Shop Owner</div>
            </div>
        </div>
    </div>
    
    <div class="page-number">
        Page 1 of 1 | Generated on {{ now()->format('M d, Y h:i A') }}
    </div>
</body>
</html> 