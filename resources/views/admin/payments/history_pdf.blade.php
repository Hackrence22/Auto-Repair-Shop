<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Payment History PDF</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f4f4f4; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 12px; color: #fff; }
        .bg-success { background: #28a745; }
        .bg-secondary { background: #6c757d; }
        .bg-danger { background: #dc3545; }
        .bg-warning { background: #ffc107; color: #333; }
        .bg-info { background: #17a2b8; }
    </style>
</head>
<body>
    <h2>Admin Payment History</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Time</th>
                <th>Payment Method</th>
                <th>Status</th>
                <th>Payment Status</th>
                <th>Reference</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
            <tr>
                <td>{{ $payment->id }}</td>
                <td>{{ $payment->customer_name }}</td>
                <td>{{ $payment->appointment_date }}</td>
                <td>{{ $payment->appointment_time }}</td>
                <td>{{ $payment->paymentMethod ? $payment->paymentMethod->name : '-' }}</td>
                <td>
                    <span class="badge bg-{{ $payment->status === 'pending' ? 'warning' : ($payment->status === 'confirmed' ? 'info' : ($payment->status === 'completed' ? 'success' : 'secondary')) }}">
                        {{ ucfirst($payment->status) }}
                    </span>
                </td>
                <td>
                    @php
                        $paymentStatusClass = [
                            'unpaid' => 'secondary',
                            'paid' => 'success',
                            'rejected' => 'danger'
                        ][$payment->payment_status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $paymentStatusClass }}">
                        {{ ucfirst($payment->payment_status) }}
                    </span>
                </td>
                <td>{{ $payment->reference_number ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html> 