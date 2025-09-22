@extends('layouts.app')

@section('title', $service->name)

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-tools me-2"></i>{{ $service->name }}</h5>
                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Back
                    </a>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted">Description</h6>
                        <p class="mb-0">{{ $service->description ?: 'No description available.' }}</p>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="p-3 border rounded">
                                <div class="text-muted">Price</div>
                                <div class="fw-bold">₱{{ number_format($service->price, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded">
                                <div class="text-muted">Duration</div>
                                <div class="fw-bold">
                                    @php
                                        $mins = (int) ($service->duration ?? 60);
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
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded">
                                <div class="text-muted">Type</div>
                                <div class="fw-bold text-capitalize">{{ $service->type ?? 'service' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted">Shop</h6>
                        @if($service->shop)
                            <div class="d-flex align-items-center">
                                <i class="fas fa-store text-primary me-2"></i>
                                <div>
                                    <div class="fw-bold">{{ $service->shop->name }}</div>
                                    <small class="text-muted">{{ $service->shop->full_address }}</small>
                                </div>
                            </div>
                        @else
                            <div class="text-muted">N/A</div>
                        @endif
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('appointments.create', ['service_id' => $service->id, 'shop' => $service->shop_id]) }}" class="btn btn-primary">
                            <i class="fas fa-calendar-plus me-1"></i>Book this Service
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


