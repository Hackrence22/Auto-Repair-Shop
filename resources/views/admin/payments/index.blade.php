@extends('layouts.admin')

@section('title', 'Payment Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-md-center" style="gap:0.75rem;">
            <div>
                <h1 class="fw-bold mb-1" style="font-size:2rem; color:#2c3e50;">Payment Management</h1>
                <p class="mb-0 text-secondary">Manage and confirm customer payments by shop</p>
            </div>
            <form method="GET" action="{{ route('admin.payments.index') }}" class="d-flex" role="search">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Search date, customer, method, ref, status...">
                </div>
            </form>
        </div>
    </div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @foreach($paymentsByShop as $shopName => $paymentsGroup)
        <h4 class="mt-4 mb-3 text-primary"><i class="fas fa-store me-2"></i>{{ $shopName }}</h4>
        <div class="card mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th>Payment Status</th>
                                <th>Reference</th>
                                <th>Proof</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($paymentsGroup as $payment)
                            <tr>
                                <td>{{ $payment->id }}</td>
                                <td>{{ $payment->customer_name }}</td>
                                <td>
                                    @php
                                        $appointmentDate = \Carbon\Carbon::parse($payment->appointment_date);
                                        $appointmentTime = \Carbon\Carbon::parse($payment->appointment_time);
                                        $combinedDateTime = $appointmentDate->setTimeFrom($appointmentTime);
                                    @endphp
                                    {{ $combinedDateTime->format('M d, Y') }}<br>
                                    <small class="text-muted">{{ $combinedDateTime->format('h:i A') }}</small>
                                </td>
                                <td>{{ $payment->paymentMethod ? $payment->paymentMethod->name : '-' }}</td>
                                <td>
                                    @php
                                        $statusClass = match($payment->status) {
                                            'pending' => 'warning',
                                            'approved' => 'info',
                                            'confirmed' => 'primary',
                                            'completed' => 'success',
                                            'cancelled' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }}">{{ ucfirst($payment->status) }}</span>
                                </td>
                                <td>
                                    @php
                                        $paymentStatusClass = match($payment->payment_status) {
                                            'unpaid' => 'secondary',
                                            'paid' => 'success',
                                            'rejected' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $paymentStatusClass }}">{{ ucfirst($payment->payment_status) }}</span>
                                </td>
                                <td>{{ $payment->reference_number ?? '-' }}</td>
                                <td>
                                    @if($payment->payment_proof)
                                        @php
                                            $proofUrl = \App\Services\ImageService::getPaymentProofImageUrl($payment->payment_proof);
                                        @endphp
                                        <a href="{{ $proofUrl }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-image"></i> View
                                        </a>
                                    @else
                                        <span class="text-muted">No proof</span>
                                    @endif
                                </td>
                                <td>
                                    @if($payment->payment_status === 'unpaid')
                                        @php $isCash = strtolower($payment->paymentMethod->role_type ?? '') === 'cash'; @endphp
                                        @if($payment->payment_proof)
                                            <div class="d-flex gap-1">
                                                <form action="{{ route('admin.payments.confirm', $payment->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Confirm this payment?')">
                                                        <i class="fas fa-check"></i> Confirm
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.payments.reject', $payment->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Reject this payment?')">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                </form>
                                            </div>
                                        @elseif($isCash)
                                            <form action="{{ route('admin.payments.confirm', $payment->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Mark this cash payment as paid?')">
                                                    <i class="fas fa-money-bill-wave"></i> Mark as Paid
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small">Waiting for proof</span>
                                        @endif
                                    @elseif($payment->payment_status === 'paid')
                                        <span class="text-success"><i class="fas fa-check-circle"></i> Confirmed</span>
                                    @elseif($payment->payment_status === 'rejected')
                                        <div class="d-flex flex-column align-items-start gap-1">
                                            <span class="text-warning small"><i class="fas fa-exclamation-circle"></i> Rejected</span>
                                            <form action="{{ route('admin.payments.confirm', $payment->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-success" onclick="return confirm('Re-confirm this payment?')">
                                                    <i class="fas fa-check"></i> Re-confirm
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-muted">No action needed</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">No payments found for this shop.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
    @if(isset($payments) && method_exists($payments, 'links'))
        <div class="d-flex justify-content-center mt-2">
            {{ $payments->withQueryString()->onEachSide(1)->links('vendor.pagination.shops') }}
        </div>
        <div class="text-center text-muted small mt-1">
            Showing {{ $payments->firstItem() }} to {{ $payments->lastItem() }} of {{ $payments->total() }} results
        </div>
    @endif
</div>
@endsection 