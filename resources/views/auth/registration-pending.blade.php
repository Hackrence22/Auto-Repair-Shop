@extends('layouts.app')

@section('title', 'Verify Your Email')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white d-flex align-items-center">
                    <i class="fas fa-shield-alt me-2"></i>
                    <h4 class="mb-0">Confirm your email to continue</h4>
                </div>
                <div class="card-body p-4">
                    @include('partials.alerts')
                    <div class="d-flex align-items-start" style="gap: 1rem;">
                        <div class="text-primary" style="font-size:2rem; line-height:1;">
                            <i class="fas fa-envelope-open-text"></i>
                        </div>
                        <div>
                            <p class="lead mb-2">We've sent a verification link to:</p>
                            <p class="fw-bold mb-3">{{ $email }}</p>
                            
                            @if($email !== 'clarencelisondra45@gmail.com')
                                <div class="alert alert-info mb-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Note:</strong> Due to email service limitations, the verification link has been sent to our admin email. 
                                    We will verify your account manually within a few minutes. You can also try logging in directly - 
                                    your account may already be activated!
                                </div>
                            @else
                                <p class="mb-3 text-muted">Please check your inbox (and spam folder) and click the verification link to complete your registration.</p>
                            @endif
                            
                            <p class="mb-4 small text-muted">The link will expire in 24 hours. If it expires, you can register again or resend the email below.</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-center" style="gap: 0.5rem;">
                        <form method="POST" action="{{ route('registration.resend') }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="email" value="{{ $email }}">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Resend Verification Email
                            </button>
                        </form>
                        <a href="{{ route('register') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-user-plus me-2"></i>Back to Register
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


