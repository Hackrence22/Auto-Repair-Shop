@include('emails.partials.header')
<div style="font-family:Arial,sans-serif;color:#2c3e50;padding:20px;">
    <h2 style="margin:0 0 8px 0;">Payment {{ ucfirst($status) }}</h2>
    <p style="margin:0 0 16px 0;">Hi {{ $user_name }}, your payment for Appointment #{{ $appointment_id }} has been {{ $status }}.</p>
    <div style="background:#f8f9fa;border:1px solid #e9ecef;border-radius:8px;padding:12px 16px;margin-bottom:16px;">
        <div><strong>Amount:</strong> ₱{{ number_format($amount, 2) }}</div>
        @if(!empty($reference))<div><strong>Reference #:</strong> {{ $reference }}</div>@endif
        @if(!empty($note))<div><strong>Note:</strong> {{ $note }}</div>@endif
    </div>
    <p style="margin:0 0 8px 0;">You can view your payment history in your account anytime.</p>
</div>
@include('emails.partials.footer')


