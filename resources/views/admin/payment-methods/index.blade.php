@extends('layouts.admin')

@section('title', 'Payment Methods')

@section('content')
<div class="container-fluid">
    <!-- Redesigned Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center p-4 mb-3 shadow-sm bg-white rounded-4" style="min-height: 110px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                        <i class="fas fa-credit-card text-white fs-2"></i>
                    </div>
                    <div>
                        <h1 class="fw-bold mb-1" style="font-size:2rem; color:#2c3e50;">Payment Methods</h1>
                        <p class="mb-0 text-secondary">Manage payment options for your customers</p>
                    </div>
                </div>
                <a href="{{ route('admin.payment-methods.create') }}" class="btn btn-lg px-4 py-2 text-white fw-semibold shadow-sm" style="background: linear-gradient(90deg,#764ba2 0%,#667eea 100%); border-radius: 12px;">
                    <i class="fas fa-plus me-2"></i> Add Payment Method
                </a>
            </div>
        </div>
    </div>

    <!-- Redesigned Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="shadow-sm rounded-4 p-3 d-flex align-items-center gap-3 bg-white">
                <div class="bg-primary bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                    <i class="fas fa-credit-card text-white"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4 text-primary">{{ $paymentMethods->count() }}</div>
                    <div class="text-secondary small">Total Methods</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="shadow-sm rounded-4 p-3 d-flex align-items-center gap-3 bg-white">
                <div class="bg-success bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                    <i class="fas fa-check-circle text-white"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4 text-success">{{ $paymentMethods->where('is_active', true)->count() }}</div>
                    <div class="text-secondary small">Active Methods</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="shadow-sm rounded-4 p-3 d-flex align-items-center gap-3 bg-white">
                <div class="bg-primary bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                    <i class="fas fa-mobile-alt text-white"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4 text-primary">{{ $paymentMethods->where('role_type', 'gcash')->count() }}</div>
                    <div class="text-secondary small">GCash Methods</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="shadow-sm rounded-4 p-3 d-flex align-items-center gap-3 bg-white">
                <div class="bg-success bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                    <i class="fas fa-mobile-alt text-white"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4 text-success">{{ $paymentMethods->where('role_type', 'paymaya')->count() }}</div>
                    <div class="text-secondary small">PayMaya Methods</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="shadow-sm rounded-4 p-3 d-flex align-items-center gap-3 bg-white">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:#32CD32;">
                    <i class="fas fa-money-bill text-white"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4" style="color:#32CD32;">{{ $paymentMethods->where('role_type', 'cash')->count() }}</div>
                    <div class="text-secondary small">Cash Methods</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="row">
        <div class="col-12">
            <div class="content-card">
                <div class="content-card-header">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center" style="gap:0.75rem;">
                        <h3 class="content-card-title mb-0">
                            <i class="fas fa-list me-2"></i>
                            Payment Methods List
                        </h3>
                        <form method="GET" action="{{ route('admin.payment-methods.index') }}" class="d-flex" role="search">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Search name, type, account...">
                            </div>
                        </form>
                    </div>
                </div>
                <div class="content-card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover modern-table">
                            <thead class="table-header">
                                <tr>
                                    <th class="text-center" style="width: 60px;">#</th>
                                    <th style="width: 80px;">Image</th>
                                    <th>Name</th>
                                    <th style="width: 120px;">Type</th>
                                    <th style="width: 150px;">Account Details</th>
                                    <th>Description</th>
                                    <th style="width: 100px;">Status</th>
                                    <th style="width: 100px;">Order</th>
                                    <th style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paymentMethods as $paymentMethod)
                                    <tr class="table-row">
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark">{{ $paymentMethod->id }}</span>
                                        </td>
                                        <td>
                                            <div class="image-container">
                                                <img src="{{ $paymentMethod->image_url }}" 
                                                     alt="{{ $paymentMethod->name }}" 
                                                     class="payment-method-image" 
                                                     style="width:60px;height:60px;object-fit:cover;border-radius:12px;border:2px solid #e9ecef;box-shadow:0 2px 8px rgba(0,0,0,0.1);cursor:pointer;"
                                                     onclick="showPaymentMethodModal({{ $paymentMethod->id }}, '{{ addslashes($paymentMethod->name) }}', '{{ $paymentMethod->image_url }}', '{{ $paymentMethod->role_type_label }}', '{{ $paymentMethod->role_type_badge_class }}', '{{ addslashes($paymentMethod->account_name) }}', '{{ addslashes($paymentMethod->account_number) }}', '{{ addslashes($paymentMethod->description) }}', {{ $paymentMethod->is_active ? 'true' : 'false' }}, {{ $paymentMethod->sort_order }}, '{{ $paymentMethod->created_at->format('M d, Y H:i') }}', '{{ $paymentMethod->updated_at->format('M d, Y H:i') }}')"
                                                     title="Click to view details">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="payment-method-info">
                                                <h6 class="payment-method-name">{{ $paymentMethod->name }}</h6>
                                                <small class="text-muted">ID: {{ $paymentMethod->id }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge 
                                                @if($paymentMethod->role_type === 'gcash') bg-primary 
                                                @elseif($paymentMethod->role_type === 'paymaya') bg-success 
                                                @elseif($paymentMethod->role_type === 'cash') bg-success" style="background:#32CD32;color:#fff;" 
                                                @else bg-secondary @endif badge-pill">
                                                <i class="fas 
                                                    @if($paymentMethod->role_type === 'gcash' || $paymentMethod->role_type === 'paymaya') fa-mobile-alt 
                                                    @elseif($paymentMethod->role_type === 'cash') fa-money-bill 
                                                    @else fa-credit-card @endif me-1"></i>
                                                {{ $paymentMethod->role_type_label }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($paymentMethod->account_name || $paymentMethod->account_number)
                                                <div class="account-details">
                                                    @if($paymentMethod->account_name)
                                                        <div class="account-item">
                                                            <i class="fas fa-user text-primary me-1"></i>
                                                            <span class="account-label">Name:</span>
                                                            <span class="account-value">{{ $paymentMethod->account_name }}</span>
                                                        </div>
                                                    @endif
                                                    @if($paymentMethod->account_number)
                                                        <div class="account-item">
                                                            <i class="fas fa-hashtag text-success me-1"></i>
                                                            <span class="account-label">Number:</span>
                                                            <span class="account-value">{{ $paymentMethod->account_number }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-minus-circle me-1"></i>
                                                    Not specified
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="description-container">
                                                <span class="description-text">
                                                    {{ Str::limit($paymentMethod->description, 60) ?: 'No description available' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $paymentMethod->is_active ? 'bg-success' : 'bg-danger' }} status-badge">
                                                <i class="fas fa-{{ $paymentMethod->is_active ? 'check' : 'times' }} me-1"></i>
                                                {{ $paymentMethod->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="sort-order-badge">{{ $paymentMethod->sort_order }}</span>
                                        </td>
                                        <td>{{ $paymentMethod->shop ? $paymentMethod->shop->name : 'N/A' }}</td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="{{ route('admin.payment-methods.edit', $paymentMethod) }}" 
                                                   class="btn btn-sm btn-outline-primary action-btn" 
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.payment-methods.toggle-status', $paymentMethod) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-outline-warning action-btn" 
                                                            title="{{ $paymentMethod->is_active ? 'Deactivate' : 'Activate' }}">
                                                        <i class="fas fa-toggle-{{ $paymentMethod->is_active ? 'off' : 'on' }}"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.payment-methods.destroy', $paymentMethod) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this payment method?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-outline-danger action-btn" 
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5">
                                            <div class="empty-state">
                                                <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted">No Payment Methods Found</h5>
                                                <p class="text-muted">Get started by adding your first payment method.</p>
                                                <a href="{{ route('admin.payment-methods.create') }}" class="btn btn-primary">
                                                    <i class="fas fa-plus me-2"></i>Add Payment Method
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(isset($paymentMethods) && method_exists($paymentMethods, 'links'))
<div class="d-flex justify-content-center mt-3">
    {{ $paymentMethods->withQueryString()->onEachSide(1)->links('vendor.pagination.shops') }}
</div>
<div class="text-center text-muted small mt-2">
    Showing {{ $paymentMethods->firstItem() }} to {{ $paymentMethods->lastItem() }} of {{ $paymentMethods->total() }} results
</div>
@endif

<!-- Enhanced Payment Method Image Modal -->
<div class="modal fade" id="paymentMethodImageModal" tabindex="-1" aria-labelledby="paymentMethodImageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modern-modal shadow-lg rounded-4 border-0">
            <div class="modal-header" style="background: linear-gradient(90deg,#764ba2 0%,#667eea 100%); color: white; border-radius: 1rem 1rem 0 0;">
                <h5 class="modal-title d-flex align-items-center gap-2" id="paymentMethodImageModalLabel">
                    <span class="d-inline-flex align-items-center justify-content-center bg-white bg-opacity-25 rounded-circle me-2" style="width:38px;height:38px;"><i class="fas fa-credit-card text-white fs-5"></i></span>
                    <span class="fw-bold" style="font-size:1.3rem;">Payment Method Details</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" style="background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);">
                <div class="row g-4 flex-wrap">
                    <div class="col-md-5 d-flex flex-column align-items-center justify-content-center">
                        <div class="modal-image-container mb-3 position-relative">
                            <div class="position-relative" style="display:inline-block;">
                                <img src="" alt="Payment Method Image" id="modal-image" class="modal-image border border-3 border-light shadow-lg" style="max-width:100%;max-height:400px;border-radius:12px;transition:transform 0.3s;display:inline;position:relative;z-index:2;">
                                <span id="modal-image-fallback" class="image-fallback d-flex align-items-center justify-content-center bg-light text-muted position-absolute top-0 start-0" style="width:200px;height:200px;border-radius:12px;border:2px dashed #dee2e6;display:none;z-index:1;">
                                    <i class="fas fa-image fa-2x"></i>&nbsp;No image
                                </span>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mb-3">
                            <span class="badge badge-pill shadow-sm" id="modal-role-type" style="font-size:1.05rem;padding:0.5em 1.2em;"></span>
                            <span class="badge badge-pill shadow-sm d-flex align-items-center gap-2" id="modal-status" style="font-size:1.05rem;padding:0.5em 1.2em;"></span>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="mb-4 p-3 rounded-4 shadow-sm bg-white" style="box-shadow:0 4px 24px rgba(102,126,234,0.10)!important;">
                            <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:18px;height:18px;background:#2563eb;"></span>
                                <span style="font-size:1.1rem;">Basic Information</span>
                            </h6>
                            <div class="mb-2"><span class="fw-semibold text-secondary">Name:</span> <span id="modal-name"></span></div>
                            <div class="mb-2"><span class="fw-semibold text-secondary">Sort Order:</span> <span id="modal-sort-order"></span></div>
                            <div class="mb-2"><span class="fw-semibold text-secondary">Created:</span> <span id="modal-created-at"></span></div>
                            <div class="mb-2"><span class="fw-semibold text-secondary">Updated:</span> <span id="modal-updated-at"></span></div>
                        </div>
                        <div class="mb-2 p-3 rounded-4 shadow-sm bg-white" style="box-shadow:0 4px 24px rgba(16,185,129,0.10)!important;">
                            <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:18px;height:18px;background:#22c55e;"></span>
                                <span style="font-size:1.1rem;">Account Details</span>
                            </h6>
                            <div class="mb-2"><span class="fw-semibold text-secondary">Account Name:</span> <span id="modal-account-name"></span></div>
                            <div class="mb-2 d-flex align-items-center gap-2">
                                <span class="fw-semibold text-secondary">Account Number:</span>
                                <span id="modal-account-number"></span>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 ms-2 position-relative d-flex align-items-center" id="copyAccountNumberBtn" title="Copy to clipboard" style="border-radius:6px;">
                                    <i class="fas fa-copy"></i>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success text-white" id="copyTooltip" style="display:none;font-size:0.8em;">Copied!</span>
                                </button>
                            </div>
                            <div class="mb-2"><span class="fw-semibold text-secondary">Description:</span> <span id="modal-description"></span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light rounded-bottom-4">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Page Header */
.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 15px;
    margin-bottom: 2rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.page-subtitle {
    font-size: 1.1rem;
    margin: 0.5rem 0 0 0;
    opacity: 0.9;
}

/* Stats Cards */
.stat-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 1px solid #e9ecef;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
}

.stat-card-body {
    display: flex;
    align-items: center;
}

.stat-card-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    margin-right: 1rem;
}

.stat-card-content {
    flex: 1;
}

.stat-card-number {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
    color: #2c3e50;
}

.stat-card-label {
    margin: 0;
    color: #6c757d;
    font-size: 0.9rem;
    font-weight: 500;
}

/* Content Card */
.content-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
    overflow: hidden;
}

.content-card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 1.5rem;
    border-bottom: 1px solid #dee2e6;
}

.content-card-title {
    margin: 0;
    color: #2c3e50;
    font-weight: 600;
}

.content-card-body {
    padding: 1.5rem;
}

/* Modern Table */
.modern-table {
    border: none;
    background: white;
}

.table-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.table-header th {
    border: none;
    padding: 1rem;
    font-weight: 600;
    color: #495057;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.table-row {
    transition: all 0.3s ease;
    border: none;
}

.table-row:hover {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    transform: scale(1.01);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.table-row td {
    border: none;
    padding: 1rem;
    vertical-align: middle;
}

/* Image Container */
.image-container {
    display: flex;
    justify-content: center;
    align-items: center;
}

.payment-method-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid #e9ecef;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.payment-method-image:hover {
    transform: scale(1.15);
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    border-color: #007bff;
}

/* Payment Method Info */
.payment-method-info {
    display: flex;
    flex-direction: column;
}

.payment-method-name {
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
    font-size: 1rem;
}

/* Account Details */
.account-details {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.account-item {
    display: flex;
    align-items: center;
    font-size: 0.85rem;
}

.account-label {
    font-weight: 500;
    color: #6c757d;
    margin-right: 0.5rem;
    min-width: 50px;
}

.account-value {
    font-weight: 600;
    color: #2c3e50;
}

/* Description Container */
.description-container {
    max-width: 200px;
}

.description-text {
    font-size: 0.9rem;
    color: #6c757d;
    line-height: 1.4;
}

/* Badges */
.badge-pill {
    border-radius: 50px;
    padding: 0.5rem 1rem;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-badge {
    border-radius: 50px;
    padding: 0.5rem 1rem;
    font-size: 0.8rem;
    font-weight: 500;
}

.sort-order-badge {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
    text-align: center;
    display: inline-block;
    min-width: 40px;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
}

.action-btn {
    width: 35px;
    height: 35px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    border: none;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
}

.empty-state i {
    opacity: 0.5;
}

/* Modern Modal */
.modern-modal {
    border-radius: 15px;
    border: none;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.modern-modal .modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px 15px 0 0;
    padding: 1.5rem;
}

.modern-modal .modal-header .btn-close {
    filter: invert(1);
    opacity: 0.8;
}

.modern-modal .modal-body {
    padding: 2rem;
}

.modern-modal .modal-footer {
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    padding: 1.5rem;
    border-radius: 0 0 15px 15px;
}

/* Info Sections */
.info-section {
    margin-bottom: 2rem;
}

.section-title {
    color: #495057;
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 0.75rem;
    margin-bottom: 1.5rem;
    font-weight: 600;
    font-size: 1.1rem;
}

.info-grid {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.info-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.info-item.full-width {
    flex-direction: column;
    gap: 0.5rem;
}

.info-label {
    font-weight: 600;
    color: #495057;
    min-width: 120px;
    flex-shrink: 0;
}

.info-value {
    color: #2c3e50;
    flex: 1;
}

/* Modal Image */
.modal-image-container {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.modal-image {
    max-width: 100%;
    max-height: 400px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border: 2px solid #e9ecef;
}

.image-fallback {
    color: #6c757d;
    font-style: italic;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px dashed #dee2e6;
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-title {
        font-size: 2rem;
    }
    
    .stat-card {
        margin-bottom: 1rem;
    }
    
    .action-buttons {
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .action-btn {
        width: 30px;
        height: 30px;
    }
    
    .table-responsive {
        font-size: 0.9rem;
    }
}

/* Print Styles */
@media print {
    .page-header,
    .stat-card,
    .content-card-header,
    .action-buttons,
    .header-actions {
        display: none !important;
    }
    
    .content-card {
        box-shadow: none;
        border: 1px solid #000;
    }
    
    .table {
        border: 1px solid #000;
    }
    
    .table th,
    .table td {
        border: 1px solid #000;
    }
}
/* Modal image hover animation */
/* Status dot color update */
#modal-status-dot.bg-success { background: #28a745 !important; }
#modal-status-dot.bg-danger { background: #dc3545 !important; }
/* Responsive modal */
@media (max-width: 768px) {
    #paymentMethodImageModal .modal-dialog {
        max-width: 98vw;
        margin: 1.2rem auto;
    }
    #paymentMethodImageModal .modal-body {
        padding: 1rem;
    }
    #paymentMethodImageModal .modal-image-container,
    #paymentMethodImageModal .modal-image,
    #paymentMethodImageModal #modal-image-fallback {
        max-width: 100% !important;
        max-height: 300px !important;
    }
}
/* Pill badge style for modal */
#paymentMethodImageModal .badge-pill {
    border-radius: 999px!important;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    letter-spacing: 0.02em;
    border: none;
}
#paymentMethodImageModal .badge.bg-primary { background: #2563eb!important; }
#paymentMethodImageModal .badge.bg-success { background: #22c55e!important; }
#paymentMethodImageModal .badge.bg-danger { background: #ef4444!important; }
#paymentMethodImageModal .badge.bg-secondary { background: #64748b!important; }
#paymentMethodImageModal .badge[style*='background:#32CD32'] { background: #32CD32!important; color: #fff!important; }
/* Info card shadow and spacing */
#paymentMethodImageModal .shadow-sm {
    box-shadow: 0 4px 24px rgba(0,0,0,0.08)!important;
}
#paymentMethodImageModal .rounded-4 {
    border-radius: 1.2rem!important;
}
#paymentMethodImageModal .modal-body {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
}
@media (max-width: 768px) {
    #paymentMethodImageModal .modal-body {
        flex-direction: column;
        align-items: stretch;
    }
    #paymentMethodImageModal .modal-image-container,
    #paymentMethodImageModal .modal-image,
    #paymentMethodImageModal #modal-image-fallback {
        max-width: 100% !important;
        max-height: 300px !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
function exportToCSV() {
    // Implementation for CSV export
    alert('CSV export functionality will be implemented here');
}

// Enhanced hover effects for table rows
document.addEventListener('DOMContentLoaded', function() {
    const tableRows = document.querySelectorAll('.table-row');
    
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.01)';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
});

// Copy to clipboard for account number with tooltip feedback
if (document.getElementById('copyAccountNumberBtn')) {
    document.getElementById('copyAccountNumberBtn').onclick = function() {
        const accNum = document.getElementById('modal-account-number').textContent;
        if (accNum && accNum !== 'Not specified') {
            navigator.clipboard.writeText(accNum);
            const tooltip = document.getElementById('copyTooltip');
            if (tooltip) {
                tooltip.style.display = 'inline-block';
                setTimeout(() => { tooltip.style.display = 'none'; }, 1200);
            }
            this.innerHTML = '<i class="fas fa-check text-success"></i>';
            setTimeout(() => { this.innerHTML = '<i class=\'fas fa-copy\'></i><span class=\'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success text-white\' id=\'copyTooltip\' style=\'display:none;font-size:0.8em;\'>Copied!</span>'; }, 1200);
        }
    };
}

// In the modal, set badge color and icon for GCash and PayMaya
function showPaymentMethodModal(id, name, imageUrl, roleTypeLabel, roleTypeBadgeClass, accountName, accountNumber, description, isActive, sortOrder, createdAt, updatedAt) {
    console.log('Modal function called with imageUrl:', imageUrl); // Debug log
    
    // Always show the image if available, even for cash
    const modalImage = document.getElementById('modal-image');
    const modalImageFallback = document.getElementById('modal-image-fallback');
    
    if (modalImage && modalImageFallback) {
        console.log('Modal image elements found'); // Debug log
        
        // Clean the image URL - remove any escaped characters
        const cleanImageUrl = imageUrl ? imageUrl.replace(/\\/g, '') : '';
        console.log('Clean image URL:', cleanImageUrl); // Debug log
        
        if (cleanImageUrl && cleanImageUrl !== '' && cleanImageUrl !== 'null' && cleanImageUrl !== 'undefined' && cleanImageUrl !== 'No Image') {
            console.log('Setting image src to:', cleanImageUrl); // Debug log
            modalImage.src = cleanImageUrl;
            modalImage.style.display = 'inline';
            modalImageFallback.style.display = 'none';
            
            // Add error handling for image load
            modalImage.onerror = function() {
                console.log('Image failed to load, showing fallback'); // Debug log
                this.style.display = 'none';
                modalImageFallback.style.display = 'flex';
            };
            
            modalImage.onload = function() {
                console.log('Image loaded successfully'); // Debug log
            };
        } else {
            console.log('No valid image URL, showing fallback'); // Debug log
            modalImage.src = '';
            modalImage.style.display = 'none';
            modalImageFallback.style.display = 'flex';
        }
    } else {
        console.log('Modal image elements not found'); // Debug log
    }
    
    // Set role type badge with color and icon
    const modalRoleType = document.getElementById('modal-role-type');
    if (modalRoleType) {
        let badgeClass = 'bg-secondary';
        let icon = '<i class="fas fa-credit-card me-1"></i>';
        let customStyle = '';
        if (roleTypeLabel && roleTypeLabel.toLowerCase().includes('gcash')) {
            badgeClass = 'bg-primary';
            icon = '<i class="fas fa-mobile-alt me-1"></i>';
        }
        if (roleTypeLabel && roleTypeLabel.toLowerCase().includes('paymaya')) {
            badgeClass = 'bg-success';
            icon = '<i class="fas fa-mobile-alt me-1"></i>';
        }
        if (roleTypeLabel && roleTypeLabel.toLowerCase().includes('cash')) {
            badgeClass = '';
            customStyle = 'background:#32CD32;color:#fff;';
            icon = '<i class="fas fa-money-bill me-1"></i>';
        }
        modalRoleType.innerHTML = icon + (roleTypeLabel || 'N/A');
        modalRoleType.className = 'badge badge-pill shadow-sm ' + badgeClass;
        modalRoleType.style = customStyle + 'font-size:1.1rem;padding:0.6em 1.2em;';
    }

    const modalStatus = document.getElementById('modal-status');
    if (modalStatus) {
        modalStatus.innerHTML = '<i class="fas fa-toggle-' + (isActive ? 'on' : 'off') + ' me-1"></i>' + (isActive ? 'Active' : 'Inactive');
        modalStatus.className = 'badge ' + (isActive ? 'bg-success' : 'bg-danger') + ' status-badge';
    }

    const modalStatusDot = document.getElementById('modal-status-dot');
    if (modalStatusDot) {
        modalStatusDot.className = isActive ? 'bg-success' : 'bg-danger';
    }

    const modalName = document.getElementById('modal-name');
    if (modalName) modalName.textContent = name || 'N/A';
    const modalSortOrder = document.getElementById('modal-sort-order');
    if (modalSortOrder) modalSortOrder.textContent = sortOrder || 'N/A';
    const modalCreatedAt = document.getElementById('modal-created-at');
    if (modalCreatedAt) modalCreatedAt.textContent = createdAt || 'N/A';
    const modalUpdatedAt = document.getElementById('modal-updated-at');
    if (modalUpdatedAt) modalUpdatedAt.textContent = updatedAt || 'N/A';

    const modalAccountName = document.getElementById('modal-account-name');
    if (modalAccountName) modalAccountName.textContent = accountName || 'N/A';
    const modalAccountNumber = document.getElementById('modal-account-number');
    if (modalAccountNumber) {
        modalAccountNumber.textContent = accountNumber || 'Not specified';
        modalAccountNumber.parentElement.style.display = accountNumber ? 'flex' : 'none';
    }
    const modalDescription = document.getElementById('modal-description');
    if (modalDescription) modalDescription.textContent = description || 'No description available';

    // Copy to clipboard for account number
    const copyBtn = document.getElementById('copyAccountNumberBtn');
    if (copyBtn) {
        copyBtn.onclick = function() {
            const accNum = modalAccountNumber.textContent;
            if (accNum && accNum !== 'Not specified') {
                navigator.clipboard.writeText(accNum);
                const tooltip = document.getElementById('copyTooltip');
                if (tooltip) {
                    tooltip.style.display = 'inline-block';
                    setTimeout(() => { tooltip.style.display = 'none'; }, 1200);
                }
                this.innerHTML = '<i class="fas fa-check text-success"></i>';
                setTimeout(() => { this.innerHTML = '<i class=\'fas fa-copy\'></i><span class=\'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success text-white\' id=\'copyTooltip\' style=\'display:none;font-size:0.8em;\'>Copied!</span>'; }, 1200);
            }
        };
    }

    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('paymentMethodImageModal'));
    modal.show();
}

// Only one copy event handler function
function enableCopyAccountNumber() {
    const copyBtn = document.getElementById('copyAccountNumberBtn');
    if (copyBtn) {
        copyBtn.onclick = function() {
            const accNum = document.getElementById('modal-account-number').textContent;
            if (accNum && accNum !== 'Not specified') {
                navigator.clipboard.writeText(accNum);
                const tooltip = document.getElementById('copyTooltip');
                if (tooltip) {
                    tooltip.style.display = 'inline-block';
                    setTimeout(() => { tooltip.style.display = 'none'; }, 1200);
                }
                this.innerHTML = '<i class="fas fa-check text-success"></i>';
                setTimeout(() => { this.innerHTML = '<i class=\'fas fa-copy\'></i><span class=\'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success text-white\' id=\'copyTooltip\' style=\'display:none;font-size:0.8em;\'>Copied!</span>'; }, 1200);
            }
        };
    }
}
// Attach enableCopyAccountNumber every time the modal is shown
const paymentMethodModal = document.getElementById('paymentMethodImageModal');
if (paymentMethodModal) {
    paymentMethodModal.addEventListener('shown.bs.modal', enableCopyAccountNumber);
}

// Test function to manually trigger modal
function testModal() {
    showPaymentMethodModal(1, 'Test Payment Method', '/storage/payment-methods/test.jpg', 'GCash', 'bg-primary', 'Test Account', '1234567890', 'Test description', true, 1, 'Jan 1, 2024 12:00', 'Jan 1, 2024 12:00');
}

// Add test button to page (temporary for debugging)
document.addEventListener('DOMContentLoaded', function() {
    const testButton = document.createElement('button');
    testButton.textContent = 'Test Modal';
    testButton.className = 'btn btn-warning btn-sm position-fixed';
    testButton.style.cssText = 'top: 10px; right: 10px; z-index: 9999;';
    testButton.onclick = testModal;
    document.body.appendChild(testButton);
});
</script>
@endpush 