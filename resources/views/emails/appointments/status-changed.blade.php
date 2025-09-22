@include('emails.partials.header')
<div style="font-family:Arial,sans-serif;color:#2c3e50;padding:20px;">
    <h2 style="margin:0 0 8px 0;">Appointment Status Updated</h2>
    <p style="margin:0 0 16px 0;">Hi {{ $user_name }}, your appointment status has changed.</p>
    <div style="background:#f8f9fa;border:1px solid #e9ecef;border-radius:8px;padding:12px 16px;margin-bottom:16px;">
        <div><strong>New Status:</strong> {{ ucfirst($status) }}</div>
        <div><strong>Date:</strong> {{ $date }}</div>
        <div><strong>Time:</strong> {{ $time }}</div>
        @if(!empty($note))<div><strong>Note:</strong> {{ $note }}</div>@endif
    </div>
    <p style="margin:0 0 8px 0;">If you have questions, reply to this email or contact us via your account.</p>
</div>
@include('emails.partials.footer')


