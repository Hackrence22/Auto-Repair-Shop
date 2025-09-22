@extends('layouts.admin')

@section('title', 'Time Slot Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Time Slot Details</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.slot-settings.edit', $slotSetting) }}" class="btn btn-primary"><i class="fas fa-edit me-1"></i>Edit</a>
            <a href="{{ route('admin.slot-settings.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Slot Information</h5>
                    @php
                        $hours = \Carbon\Carbon::parse($slotSetting->start_time)->diffInHours(\Carbon\Carbon::parse($slotSetting->end_time));
                        $totalSlots = $hours * $slotSetting->slots_per_hour;
                    @endphp
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="fw-semibold text-muted">Time Range</td>
                            <td>{{ \Carbon\Carbon::parse($slotSetting->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($slotSetting->end_time)->format('h:i A') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Slots per Hour</td>
                            <td>{{ $slotSetting->slots_per_hour }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Total Hours</td>
                            <td>{{ $hours }} hours</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Total Slots</td>
                            <td><span class="badge bg-dark">{{ $totalSlots }} slots</span></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Status</td>
                            <td><span class="badge {{ $slotSetting->is_active ? 'bg-success' : 'bg-danger' }}">{{ $slotSetting->is_active ? 'Active' : 'Inactive' }}</span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Shop Information</h5>
                    @php $shop = $slotSetting->shop; @endphp
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


