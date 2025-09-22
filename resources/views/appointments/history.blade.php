@extends('layouts.app')

@section('title', 'Payment History')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3" style="gap:0.75rem;">
        <h2 class="mb-0">My Payment History</h2>
        <form method="GET" action="{{ route('appointments.history') }}" class="d-flex" role="search">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Search ID, date (YYYY-MM-DD), method, status, ref...">
            </div>
        </form>
    </div>
    <div class="mb-3 d-flex gap-2 flex-wrap">
        <a href="{{ route('appointments.history.csv') }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-download me-2"></i>Download CSV
        </a>
        <a href="{{ route('appointments.history.pdf') }}" class="btn btn-outline-danger btn-sm">
            <i class="fas fa-file-pdf me-2"></i>Download PDF
        </a>
    </div>
    <div class="card">
        <div class="card-body">
            <!-- Desktop Table -->
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Payment Status</th>
                            <th>Reference</th>
                            <th>Proof</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->id }}</td>
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
                                    <i class="fas fa-money-bill-wave me-1"></i>{{ ucfirst($payment->payment_status) }}
                                </span>
                            </td>
                            <td>{{ $payment->reference_number ?? '-' }}</td>
                            <td>
                                @php
                                    $proofUrl = \App\Services\ImageService::getPaymentProofImageUrl($payment->payment_proof);
                                @endphp
                                @if($payment->payment_proof)
                                    <a href="{{ $proofUrl }}" target="_blank">
                                        <img src="{{ $proofUrl }}" alt="Proof" style="width:48px; height:48px; object-fit:cover; border-radius:6px; border:1.5px solid #e9ecef;">
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No payments found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="d-md-none">
                @forelse($payments as $payment)
                    @php
                        $appointmentDate = \Carbon\Carbon::parse($payment->appointment_date);
                        $appointmentTime = \Carbon\Carbon::parse($payment->appointment_time);
                        $combinedDateTime = $appointmentDate->setTimeFrom($appointmentTime);
                        $statusClass = $payment->status === 'pending' ? 'warning' : ($payment->status === 'confirmed' ? 'info' : ($payment->status === 'completed' ? 'success' : 'secondary'));
                        $paymentStatusClass = [
                            'unpaid' => 'secondary',
                            'paid' => 'success',
                            'rejected' => 'danger'
                        ][$payment->payment_status] ?? 'secondary';
                    @endphp
                    <div class="card mb-3 payment-card status-{{ $statusClass }} pay-{{ $paymentStatusClass }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="icon-circle me-2"><i class="fas fa-receipt"></i></div>
                                    <div>
                                        <div class="fw-bold">#{{ $payment->id }}</div>
                                        <small class="text-muted">{{ $combinedDateTime->format('M d, Y') }} • {{ $combinedDateTime->format('h:i A') }}</small>
                                    </div>
                                </div>
                                <span class="badge bg-{{ $statusClass }}">{{ ucfirst($payment->status) }}</span>
                            </div>
                            <div class="row g-2 mt-1">
                                <div class="col-6">
                                    <div class="text-muted small">Method</div>
                                    <div class="fw-semibold">{{ $payment->paymentMethod ? $payment->paymentMethod->name : '-' }}</div>
                                </div>
                                <div class="col-6 text-end">
                                    <span class="badge bg-{{ $paymentStatusClass }}">
                                        <i class="fas fa-money-bill-wave me-1"></i>{{ ucfirst($payment->payment_status) }}
                                    </span>
                                </div>
                                <div class="col-12">
                                    <div class="text-muted small">Reference</div>
                                    <div class="fw-semibold">{{ $payment->reference_number ?? '-' }}</div>
                                </div>
                                <div class="col-12 d-flex align-items-center justify-content-between">
                                    <div class="text-muted small">Proof</div>
                                    <div>
                                        @php
                                            $proofUrl = \App\Services\ImageService::getPaymentProofImageUrl($payment->payment_proof);
                                        @endphp
                                        @if($payment->payment_proof)
                                            <a href="{{ $proofUrl }}" target="_blank" class="proof-link">
                                                <img src="{{ $proofUrl }}" alt="Proof" class="proof-thumb">
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted">No payments found.</div>
                @endforelse
            </div>
        </div>
        <div class="pagination-wrap d-flex flex-column align-items-center mt-2 mb-1">
            {{ $payments->withQueryString()->onEachSide(1)->links('vendor.pagination.shops') }}
            <div class="text-muted small mt-1">
                Showing {{ $payments->firstItem() }} to {{ $payments->lastItem() }} of {{ $payments->total() }} results
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
@media (max-width: 767.98px) {
    .payment-card {
        border: 1px solid #e9ecef;
        border-radius: 12px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.06);
        overflow: hidden;
        position: relative;
    }
    /* Subtle gradient accent */
    .payment-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; height: 4px;
        background: linear-gradient(90deg, #0d6efd 0%, #20c997 100%);
    }
    .payment-card.status-warning::before { background: linear-gradient(90deg, #ffc107 0%, #fd7e14 100%); }
    .payment-card.status-info::before { background: linear-gradient(90deg, #0dcaf0 0%, #0d6efd 100%); }
    .payment-card.status-success::before { background: linear-gradient(90deg, #20c997 0%, #198754 100%); }
    .payment-card.status-secondary::before { background: linear-gradient(90deg, #adb5bd 0%, #6c757d 100%); }

    .payment-card .card-body { padding: 1rem; }
    .payment-card .badge { font-size: 0.75rem; padding: 0.35rem 0.5rem; border-radius: 6px; }
    .payment-card .fw-bold { font-size: 0.95rem; }
    .payment-card .fw-semibold { font-size: 0.9rem; }
    .payment-card .text-muted.small { font-size: 0.8rem; }

    .icon-circle {
        width: 34px; height: 34px; border-radius: 50%;
        background: rgba(13,110,253,0.1);
        display: flex; align-items: center; justify-content: center;
        color: #0d6efd;
    }

    .proof-thumb {
        width: 56px; height: 56px; object-fit: cover; border-radius: 8px; border: 1.5px solid #e9ecef;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .proof-link:hover .proof-thumb { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
}
</style>
@endpush 