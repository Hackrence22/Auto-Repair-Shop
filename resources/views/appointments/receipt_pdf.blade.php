<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
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
            width: 120px;
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
            padding: 8px 10px; 
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
        .paid-badge { 
            color: #fff; 
            background: #16a34a; 
            padding: 8px 20px; 
            border-radius: 8px; 
            font-size: 14px; 
            font-weight: bold;
            display: inline-block; 
            margin: 10px 0;
            text-transform: uppercase;
        }
        .section-title { 
            font-weight: bold; 
            margin-top: 20px; 
            margin-bottom: 10px; 
            color: #0ea5e9;
            font-size: 14px;
            text-transform: uppercase;
            border-bottom: 2px solid #0ea5e9;
            padding-bottom: 5px;
        }
        .totals-container { 
            margin-top: 20px; 
            padding: 15px; 
            background: #f0f9ff; 
            border-radius: 5px; 
            border: 1px solid #bae6fd;
        }
        .total-item { 
            margin-bottom: 8px; 
            font-size: 13px; 
            font-weight: bold;
        }
        .total-item.final { 
            font-weight: bold; 
            color: #0ea5e9; 
            font-size: 16px; 
            border-top: 2px solid #bae6fd;
            padding-top: 8px;
            margin-top: 8px;
        }
        .footer { 
            margin-top: 20px; 
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
            margin-top: 20px; 
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
        .thank-you {
            text-align: center;
            margin: 20px 0;
            color: #0ea5e9;
            font-size: 16px;
            font-weight: bold;
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
            <div><span class="receipt-label">Receipt #:</span> {{ $appointment->id }}</div>
            <div><span class="receipt-label">Date:</span> {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('F d, Y') }}</div>
            <div><span class="receipt-label">Time:</span> {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}</div>
            <div><span class="receipt-label">Generated:</span> {{ now()->format('F d, Y h:i A') }}</div>
        </div>

        <div class="paid-badge">PAID</div>

        <div class="section-title">Customer Information</div>
        <table class="table">
            <tr>
                <th>Name</th>
                <td>{{ $appointment->customer_name }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $appointment->email }}</td>
            </tr>
            <tr>
                <th>Phone</th>
                <td>{{ $appointment->phone }}</td>
            </tr>
            <tr>
                <th>Vehicle</th>
                <td>{{ $appointment->vehicle_type }} - {{ $appointment->vehicle_model }} ({{ $appointment->vehicle_year }})</td>
            </tr>
        </table>

        <div class="section-title">Service & Payment Details</div>
        <table class="table">
            <tr>
                <th>Service</th>
                <td>{{ $appointment->service ? $appointment->service->name : '-' }}</td>
            </tr>
            <tr>
                <th>Payment Method</th>
                <td>{{ $appointment->paymentMethod ? $appointment->paymentMethod->name : '-' }}</td>
            </tr>
            <tr>
                <th>Reference #</th>
                <td>{{ $appointment->reference_number ?? '-' }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>Paid</td>
            </tr>
        </table>

        <div class="totals-container">
            <div class="total-item final">
                <strong>Amount Paid:</strong> PHP {{ number_format($appointment->service ? $appointment->service->price : 0, 2) }}
            </div>
        </div>

        <div class="thank-you">Thank you for your payment!</div>

        <div class="footer">
            <div class="footer-title">Terms & Conditions</div>
            <div class="footer-content">
                • Payment is due within 15 days of receipt<br>
                • Please make checks payable to: Auto Repair Shop<br>
                • All services are guaranteed for 30 days<br>
                • Cancellation requires 24-hour notice<br>
                • Prices are subject to change without prior notice<br>
                • This receipt is generated on {{ now()->format('F d, Y \a\t h:i A') }}
            </div>
        </div>

        <div class="signature-block">
            <div class="signature-container">
                <img src="{{ public_path('images/signature.png') }}" class="signature-img" alt="Signature">
                <div class="signature-name">Clarence Angelo D. Lisondra</div>
                <div class="signature-label">Shop Owner</div>
            </div>
        </div>
        
        <div class="page-number">
            Page 1 of 1 | Generated on {{ now()->format('M d, Y h:i A') }}
        </div>
    </div>
</body>
</html> 