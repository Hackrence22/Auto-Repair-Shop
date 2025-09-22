@extends('layouts.admin')

@section('title', 'Shop Ratings')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Ratings</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3 mb-0"><i class="fas fa-star me-2"></i>Shop Ratings</h1>
            </div>

            <div class="card">
                <div class="table-responsive table-scroll-y">
                    <table class="table table-hover table-sticky mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:36px"></th>
                                <th>User</th>
                                <th>Shop</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ratings as $r)
                                <tr>
                                    <td>
                                        <img src="{{ $r->user->profile_picture_url }}" onerror="this.onerror=null;this.src='{{ $r->user->avatar ?? asset('images/default-profile.png') }}';" class="rounded-circle" style="width:28px;height:28px;object-fit:cover;">
                                    </td>
                                    <td>{{ $r->user->name }}</td>
                                    <td>{{ $r->shop->name }}</td>
                                    <td>
                                        @for($i=1;$i<=5;$i++)
                                            <i class="fas fa-star" style="color: {{ $i <= $r->rating ? '#ffc107' : '#e4e5e9' }};"></i>
                                        @endfor
                                        <span class="ms-1">{{ $r->rating }}</span>
                                    </td>
                                    <td>{{ $r->comment }}</td>
                                    <td>{{ $r->created_at->format('M d, Y h:i A') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No ratings found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(method_exists($ratings, 'links'))
                <div class="card-footer d-flex justify-content-center">
                    {{ $ratings->links('vendor.pagination.shops') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection


