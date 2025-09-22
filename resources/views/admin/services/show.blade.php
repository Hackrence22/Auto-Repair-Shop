@extends('layouts.admin')

@section('title', 'Service Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Service Details</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-primary"><i class="fas fa-edit me-1"></i>Edit</a>
            <a href="{{ route('admin.services.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Service Information</h5>
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="fw-semibold text-muted">Name</td>
                            <td>{{ $service->name }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Type</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($service->type) }}</span></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Price</td>
                            <td class="fw-bold text-success">₱{{ number_format($service->price, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Duration</td>
                            <td>
                                @php
                                    $mins = (int) $service->duration;
                                    $days = intdiv($mins, 1440);
                                    $mins -= $days * 1440;
                                    $hours = intdiv($mins, 60);
                                    $mins -= $hours * 60;
                                    $parts = [];
                                    if ($days > 0) $parts[] = $days . 'd';
                                    if ($hours > 0) $parts[] = $hours . 'h';
                                    if ($mins > 0 || empty($parts)) $parts[] = $mins . 'm';
                                    echo implode(' ', $parts);
                                @endphp
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Status</td>
                            <td><span class="badge {{ $service->is_active ? 'bg-success' : 'bg-danger' }}">{{ $service->is_active ? 'Active' : 'Inactive' }}</span></td>
                        </tr>
                    </table>
                    <div class="mt-3">
                        <div class="fw-semibold text-muted mb-1">Description</div>
                        <div>{{ $service->description }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Shop Information</h5>
                    @php $shop = $service->shop; @endphp
                    @if($shop)
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="fw-semibold text-muted">Shop</td>
                            <td>{{ $shop->name }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Owner</td>
                            <td>{{ $shop->owner_name }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Phone</td>
                            <td>{{ $shop->phone }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Address</td>
                            <td>{{ $shop->full_address }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Hours</td>
                            <td>{{ $shop->operating_hours }}</td>
                        </tr>
                    </table>
                    @else
                        <div class="text-muted">No shop associated.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


