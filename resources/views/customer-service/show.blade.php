@extends('layouts.app')

@section('content')
<div class="container py-4 customer-service-show">
    <div class="mx-auto" style="max-width: 980px;">
        <div class="d-flex align-items-center mb-3">
            <a href="{{ route('customer-service.index') }}" class="btn btn-link p-0 me-2"><i class="fas fa-arrow-left"></i></a>
            <h1 class="h3 mb-0">Customer Service Request Details</h1>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                        <h2 class="h5 mb-1">{{ $customerService->subject }}</h2>
                        <div class="text-muted small">Shop: {{ $customerService->shop->name }} • Created: {{ $customerService->created_at->format('M d, Y \a\t g:i A') }}</div>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="badge {{ $customerService->category_color }}">{{ ucfirst($customerService->category) }}</span>
                        <span class="badge {{ $customerService->priority_color }}">{{ ucfirst($customerService->priority) }} Priority</span>
                        <span class="badge {{ $customerService->status_color }}">{{ ucfirst(str_replace('_', ' ', $customerService->status)) }}</span>
                </div>
            </div>

                <div class="border-top pt-3">
                    <h3 class="h6 mb-3">Your Message</h3>
                    <div class="alert alert-light border">{{ $customerService->message }}</div>
            </div>

            @if($customerService->assignedAdmin)
                    <div class="border-top pt-3 mt-2">
                        <h3 class="h6 mb-3">Assigned To</h3>
                        <div class="d-flex align-items-center">
                            <div class="badge bg-primary me-2"><i class="fas fa-user"></i></div>
                            <div>
                                <div class="fw-semibold">{{ $customerService->assignedAdmin->name }}</div>
                                <div class="text-muted small">Admin</div>
                        </div>
                        </div>
                    </div>
                @endif
                </div>
        </div>

        @if($customerService->admin_reply)
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="badge bg-success me-2"><i class="fas fa-check"></i></div>
                    <div>
                            <div class="fw-semibold">Admin Response</div>
                            <div class="text-muted small">
                            @if($customerService->assignedAdmin)
                                From {{ $customerService->assignedAdmin->name }}
                            @endif
                            @if($customerService->updated_at)
                                • {{ $customerService->updated_at->format('M d, Y \a\t g:i A') }}
                            @endif
                        </div>
                    </div>
                </div>
                    <div class="alert alert-success mb-0">{{ $customerService->admin_reply }}</div>
                </div>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body">
                <h3 class="h6 mb-3">Request Timeline</h3>
                <div class="mb-3 d-flex">
                    <div class="me-2"><span class="badge bg-primary"><i class="fas fa-clock"></i></span></div>
                    <div>
                        <div class="fw-semibold">Request Submitted</div>
                        <div class="text-muted small">{{ $customerService->created_at->format('M d, Y \a\t g:i A') }}</div>
                        <div class="text-muted small">Category: {{ ucfirst($customerService->category) }}</div>
                    </div>
                </div>
                @if($customerService->assignedAdmin)
                    <div class="mb-3 d-flex">
                        <div class="me-2"><span class="badge bg-warning text-dark"><i class="fas fa-user"></i></span></div>
                        <div>
                            <div class="fw-semibold">Assigned to Admin</div>
                            <div class="text-muted small">{{ $customerService->assignedAdmin->name }}</div>
                        </div>
                    </div>
                @endif
                @if($customerService->admin_reply)
                    <div class="mb-3 d-flex">
                        <div class="me-2"><span class="badge bg-success"><i class="fas fa-reply"></i></span></div>
                        <div>
                            <div class="fw-semibold">Admin Responded</div>
                            <div class="text-muted small">{{ $customerService->updated_at->format('M d, Y \a\t g:i A') }}</div>
                        </div>
                    </div>
                @endif
                @if($customerService->status === 'resolved' && $customerService->resolved_at)
                    <div class="d-flex">
                        <div class="me-2"><span class="badge bg-success"><i class="fas fa-check"></i></span></div>
                        <div>
                            <div class="fw-semibold">Request Resolved</div>
                            <div class="text-muted small">{{ $customerService->resolved_at->format('M d, Y \a\t g:i A') }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
