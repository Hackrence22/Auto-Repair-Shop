<div style="font-family: Arial, sans-serif; color: #2c3e50;">
    <h2 style="color:#0d6efd;">Confirm your email</h2>
    <p>Hi {{ $name }},</p>
    <p>Thanks for signing up to Auto Repair Shop. Please confirm your email to finish creating your account.</p>
    <p style="margin:24px 0;">
        <a href="{{ $verifyUrl }}" style="background:#0d6efd;color:#fff;padding:12px 18px;border-radius:8px;text-decoration:none;display:inline-block;">Verify Email</a>
    </p>
    <p>If the button doesn't work, copy and paste this link into your browser:</p>
    <p style="word-break: break-all; color:#0d6efd;">{{ $verifyUrl }}</p>
    <hr style="border:none;border-top:1px solid #eee;margin:16px 0;" />
    <p style="font-size:12px;color:#6c757d;">If you didn't request this, you can ignore this email.</p>
</div>


