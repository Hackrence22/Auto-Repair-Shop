@extends('layouts.admin')

@section('title', 'Admin Payment History')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3" style="gap:0.75rem;">
        <h2 class="mb-0">All Payment History by Shop</h2>
        <form method="GET" action="{{ route('admin.payments.history') }}" class="d-flex" role="search">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Search ID, date, customer, method, ref, status...">
            </div>
        </form>
    </div>
    <div class="mb-3">
        <a href="{{ route('admin.payments.history.csv') }}" class="btn btn-outline-primary">
            <i class="fas fa-download me-2"></i>Download CSV
        </a>
        <a href="{{ route('admin.payments.history.pdf') }}" class="btn btn-outline-danger ms-2">
            <i class="fas fa-file-pdf me-2"></i>Download PDF
        </a>
    </div>

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
                                        <a href="{{ \App\Services\ImageService::getPaymentProofImageUrl($payment->payment_proof) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-image"></i> View
                                        </a>
                                    @else
                                        <span class="text-muted">No proof</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No payment history found for this shop.</td>
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
        <div class="text-center text-muted small mt-2">
            Showing {{ $payments->firstItem() }} to {{ $payments->lastItem() }} of {{ $payments->total() }} results
        </div>
    @endif
</div>
@endsection 