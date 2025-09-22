@php($s=$shop??null)
@include('emails.partials.header')
<div style="font-family:Arial,sans-serif;color:#2c3e50;padding:20px;">
    <h2 style="margin:0 0 8px 0;">Your Appointment is Confirmed</h2>
    <p style="margin:0 0 16px 0;">Hi {{ $user_name }}, thanks for booking with {{ $s? $s->name : 'our shop' }}.</p>
    <div style="background:#f8f9fa;border:1px solid #e9ecef;border-radius:8px;padding:12px 16px;margin-bottom:16px;">
        <div><strong>Date:</strong> {{ $date }}</div>
        <div><strong>Time:</strong> {{ $time }}</div>
        @if(!empty($service))<div><strong>Service:</strong> {{ $service }}</div>@endif
        @if($s)
            <div><strong>Shop:</strong> {{ $s->name }}</div>
            <div><strong>Address:</strong> {{ $s->full_address }}</div>
        @endif
    </div>
    <p style="margin:0 0 8px 0;">You can view or manage your appointment anytime from your account.</p>
</div>
@include('emails.partials.footer')


