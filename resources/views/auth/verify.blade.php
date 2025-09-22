@extends('layouts.app')

@section('title', 'Verify Your Email')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-envelope-open-text me-2"></i>Email Verification Required</h4>
                </div>
                <div class="card-body p-4">
                    @include('partials.alerts')
                    <p class="lead mb-3">We sent a verification link to your email. Please check your inbox (and spam) to complete your registration.</p>
                    <p class="mb-4">Didn't receive it? You can request another verification email below.</p>
                    <form method="POST" action="{{ route('verification.resend') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Resend Verification Email
                        </button>
                    </form>
                    <a href="{{ route('register') }}" class="btn btn-outline-secondary ms-2">
                        <i class="fas fa-user-plus me-2"></i>Back to Register
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Verify Your Email Address') }}</div>

                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ __('A fresh verification link has been sent to your email address.') }}
                        </div>
                    @endif

                    {{ __('Before proceeding, please check your email for a verification link.') }}
                    {{ __('If you did not receive the email') }},
                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('click here to request another') }}</button>.
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
