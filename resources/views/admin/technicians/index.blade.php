@extends('layouts.admin')

@section('title', 'Technicians Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center p-4 mb-3 shadow-sm bg-white rounded-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                        <i class="fas fa-user-cog text-white fs-2"></i>
                    </div>
                    <div>
                        <h1 class="fw-bold mb-1" style="font-size:2rem; color:#2c3e50;">Technicians Management</h1>
                        <p class="mb-0 text-secondary">Manage your shop technicians and their profiles</p>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.technicians.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add Technician
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Technicians List -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-users me-2 text-primary"></i>
                            Technicians ({{ $technicians->total() }})
                        </h5>
                    </div>
                    
                    <!-- Search and Filters -->
                    <div class="filter-section">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" id="searchInput" placeholder="Search technicians...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" id="shopFilter">
                                    <option value="">All Shops</option>
                                    @foreach($shops as $shop)
                                        <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" id="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="on_leave">On Leave</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" id="availabilityFilter">
                                    <option value="">All Availability</option>
                                    <option value="1">Available</option>
                                    <option value="0">Unavailable</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-secondary w-100" id="clearFilters">
                                    <i class="fas fa-times me-2"></i>Clear
                                </button>
                            </div>
                            
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($technicians->count() > 0)
                        @php
                            $groupedTechnicians = $technicians->groupBy('shop_id');
                        @endphp
                        
                        @foreach($groupedTechnicians as $shopId => $shopTechnicians)
                            @php
                                $shop = $shopTechnicians->first()->shop;
                            @endphp
                            
                            <!-- Shop Section -->
                            <div class="shop-section" data-shop-id="{{ $shop->id }}">
                                <!-- Shop Header -->
                                <div class="shop-header bg-light border-bottom p-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-store text-primary"></i>
                                        <h6 class="mb-0 fw-bold text-dark">{{ $shop->name }}</h6>
                                        <span class="badge bg-primary">{{ $shopTechnicians->count() }} technician(s)</span>
                                    </div>
                                </div>
                                
                                <!-- Technicians for this shop -->
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="border-0">Technician</th>
                                                <th class="border-0">Specialization</th>
                                                <th class="border-0">Experience</th>
                                                <th class="border-0">Status</th>
                                                <th class="border-0">Availability</th>
                                                <th class="border-0">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($shopTechnicians as $technician)
                                            <tr class="technician-row" 
                                                data-shop="{{ $technician->shop_id }}"
                                                data-status="{{ $technician->status }}"
                                                data-availability="{{ $technician->is_available ? '1' : '0' }}"
                                                data-search="{{ strtolower($technician->name . ' ' . $technician->email . ' ' . ($technician->specialization ?? '') . ' ' . $technician->phone) }}">
                                                <td>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="technician-avatar">
                                                            <img src="{{ $technician->profile_picture_url }}" 
                                                                 alt="{{ $technician->name }}" 
                                                                 class="rounded-circle"
                                                                 width="50" height="50"
                                                                 style="object-fit: cover;">
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1 fw-bold text-dark">{{ $technician->name }}</h6>
                                                            <small class="text-muted">{{ $technician->email }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($technician->specialization)
                                                        <span class="text-dark">{{ $technician->specialization }}</span>
                                                    @else
                                                        <span class="text-muted">Not specified</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">{{ $technician->experience_text }}</span>
                                                </td>
                                                <td>
                                                    {!! $technician->status_badge !!}
                                                </td>
                                                <td>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input availability-toggle" 
                                                               type="checkbox" 
                                                               data-technician-id="{{ $technician->id }}"
                                                               {{ $technician->is_available ? 'checked' : '' }}>
                                                        <label class="form-check-label">
                                                            {{ $technician->is_available ? 'Available' : 'Unavailable' }}
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('admin.technicians.show', $technician) }}" 
                                                           class="btn btn-sm btn-outline-primary" title="View Profile">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('admin.technicians.edit', $technician) }}" 
                                                           class="btn btn-sm btn-outline-warning" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-danger delete-technician" 
                                                                data-technician-id="{{ $technician->id }}"
                                                                data-technician-name="{{ $technician->name }}"
                                                                title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                        
                        <!-- Pagination -->
                        <div class="card-footer bg-white border-0 py-3">
                            {{ $technicians->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-user-cog fa-3x text-muted"></i>
                            </div>
                            <h5 class="text-muted">No Technicians Found</h5>
                            <p class="text-muted">Start by adding your first technician to manage appointments.</p>
                            <a href="{{ route('admin.technicians.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Add Technician
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteTechnicianModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="technicianName"></strong>?</p>
                <p class="text-danger small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteTechnicianForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.technician-avatar img {
    border: 2px solid #e9ecef;
    transition: border-color 0.3s ease;
}

.technician-avatar img:hover {
    border-color: #007bff;
}

.availability-toggle:checked {
    background-color: #28a745;
    border-color: #28a745;
}

.form-check-input:focus {
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.shop-section {
    margin-bottom: 1rem;
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.shop-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-left: 4px solid #007bff;
    margin: 0;
    padding: 1rem;
}

.shop-header h6 {
    color: #495057;
    font-weight: 600;
}

.shop-header .badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.table-responsive {
    border: none;
    background: white;
}

.technician-row {
    transition: background-color 0.2s ease;
}

.technician-row:hover {
    background-color: #f8f9fa;
}

.no-results-message {
    background: #f8f9fa;
    border-radius: 0.5rem;
    margin: 1rem;
}

/* Filter styles */
.filter-section {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1rem;
}

.filter-section .form-control,
.filter-section .form-select {
    border-radius: 0.375rem;
}

.filter-section .btn {
    border-radius: 0.375rem;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Technician management page loaded');
    
    // Check if jQuery is available
    if (typeof $ === 'undefined') {
        console.error('jQuery is not loaded!');
        return;
    }
    
    // Get filter elements
    const searchInput = document.getElementById('searchInput');
    const shopFilter = document.getElementById('shopFilter');
    const statusFilter = document.getElementById('statusFilter');
    const availabilityFilter = document.getElementById('availabilityFilter');
    const clearFiltersBtn = document.getElementById('clearFilters');
    
    // Check if all elements exist
    if (!searchInput || !shopFilter || !statusFilter || !availabilityFilter || !clearFiltersBtn) {
        console.error('One or more filter elements not found:', {
            searchInput: !!searchInput,
            shopFilter: !!shopFilter,
            statusFilter: !!statusFilter,
            availabilityFilter: !!availabilityFilter,
            clearFiltersBtn: !!clearFiltersBtn
        });
        return;
    }
    
    console.log('All filter elements found successfully');
    
    // Set current filter values from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    $(shopFilter).val(urlParams.get('shop') || '');
    $(statusFilter).val(urlParams.get('status') || '');
    $(availabilityFilter).val(urlParams.get('availability') || '');
    $(searchInput).val(urlParams.get('search') || '');

    console.log('Initial filter values set:', {
        shop: $(shopFilter).val(),
        status: $(statusFilter).val(),
        availability: $(availabilityFilter).val(),
        search: $(searchInput).val()
    });

    // Debug: Check data attributes
    console.log('Technician rows found:', $('.technician-row').length);
    $('.technician-row').each(function(index) {
        const $row = $(this);
        console.log(`Row ${index}:`, {
            shop: $row.attr('data-shop'),
            status: $row.attr('data-status'),
            availability: $row.attr('data-availability'),
            search: $row.attr('data-search')
        });
    });

    // Apply initial filters
    filterTechnicians();

    // Availability toggle
    $('.availability-toggle').on('change', function() {
        const technicianId = $(this).data('technician-id');
        const isChecked = $(this).is(':checked');
        const $toggle = $(this);
        
        $.ajax({
            url: `/admin/technicians/${technicianId}/toggle-availability`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Update label
                    const label = $toggle.siblings('label');
                    label.text(response.is_available ? 'Available' : 'Unavailable');
                    
                    // Update data attribute
                    $toggle.closest('tr').attr('data-availability', response.is_available ? '1' : '0');
                    
                    // Show success message
                    showNotification(response.message, 'success');
                }
            },
            error: function() {
                // Revert toggle on error
                $toggle.prop('checked', !isChecked);
                showNotification('Failed to update availability', 'error');
            }
        });
    });

    // Delete technician
    $('.delete-technician').on('click', function() {
        const technicianId = $(this).data('technician-id');
        const technicianName = $(this).data('technician-name');
        
        $('#technicianName').text(technicianName);
        $('#deleteTechnicianForm').attr('action', `/admin/technicians/${technicianId}`);
        $('#deleteTechnicianModal').modal('show');
    });

    // Search functionality
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            filterTechnicians();
        }, 300);
    });

    // Shop filter
    shopFilter.addEventListener('change', function() {
        filterTechnicians();
    });

    // Status filter
    statusFilter.addEventListener('change', function() {
        filterTechnicians();
    });

    // Availability filter
    availabilityFilter.addEventListener('change', function() {
        filterTechnicians();
    });

    // Clear filters
    clearFiltersBtn.addEventListener('click', function() {
        console.log('Clear filters button clicked');
        searchInput.value = '';
        shopFilter.value = '';
        statusFilter.value = '';
        availabilityFilter.value = '';
        filterTechnicians();
    });
    
    // Removed test filter button

    function filterTechnicians() {
        const search = searchInput.value.toLowerCase();
        const shop = shopFilter.value;
        const status = statusFilter.value;
        const availability = availabilityFilter.value;
        
        console.log('Filtering with values:', { search, shop, status, availability });
        
        // Show all shop sections first
        const shopSections = document.querySelectorAll('.shop-section');
        shopSections.forEach(section => section.style.display = '');
        console.log('All shop sections shown');
        
        // Apply filters to technician rows
        let hiddenCount = 0;
        const technicianRows = document.querySelectorAll('.technician-row');
        console.log('Found technician rows:', technicianRows.length);
        
        technicianRows.forEach(function(row, index) {
            let showRow = true;
            
            // Search filter
            if (search) {
                const searchText = row.getAttribute('data-search');
                console.log(`Row ${index} search text:`, searchText);
                if (!searchText || !searchText.includes(search)) {
                    showRow = false;
                }
            }
            
            // Shop filter
            if (shop) {
                const rowShop = row.getAttribute('data-shop');
                console.log(`Row ${index} shop:`, rowShop, 'Filter shop:', shop);
                if (rowShop !== shop) {
                    showRow = false;
                }
            }
            
            // Status filter
            if (status) {
                const rowStatus = row.getAttribute('data-status');
                console.log(`Row ${index} status:`, rowStatus, 'Filter status:', status);
                if (rowStatus !== status) {
                    showRow = false;
                }
            }
            
            // Availability filter
            if (availability) {
                const rowAvailability = row.getAttribute('data-availability');
                console.log(`Row ${index} availability:`, rowAvailability, 'Filter availability:', availability);
                if (rowAvailability !== availability) {
                    showRow = false;
                }
            }
            
            if (showRow) {
                row.style.display = '';
                console.log(`Row ${index} shown`);
            } else {
                row.style.display = 'none';
                hiddenCount++;
                console.log(`Row ${index} hidden`);
            }
        });
        
        console.log('Filtering complete:', { hiddenCount, totalRows: technicianRows.length });
        
        // Hide empty shop sections
        shopSections.forEach(function(shopSection) {
            const visibleRows = shopSection.querySelectorAll('.technician-row[style*="display: none"]').length;
            const totalRows = shopSection.querySelectorAll('.technician-row').length;
            const actuallyVisible = totalRows - visibleRows;
            
            console.log('Shop section:', shopSection, 'Visible rows:', actuallyVisible, 'Total rows:', totalRows);
            
            if (actuallyVisible === 0) {
                shopSection.style.display = 'none';
                console.log('Shop section hidden');
            } else {
                shopSection.style.display = '';
                console.log('Shop section shown');
            }
        });
        
        // Show "no results" message if no technicians are visible
        const totalVisible = document.querySelectorAll('.technician-row[style*="display: none"]').length;
        const totalRows = document.querySelectorAll('.technician-row').length;
        const actuallyVisible = totalRows - totalVisible;
        
        console.log('Total visible technicians:', actuallyVisible, 'Total rows:', totalRows);
        
        if (actuallyVisible === 0) {
            if (document.querySelector('.no-results-message') === null) {
                const cardBody = document.querySelector('.card-body');
                if (cardBody) {
                    cardBody.insertAdjacentHTML('beforeend', `
                        <div class="no-results-message text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-search fa-3x text-muted"></i>
                            </div>
                            <h5 class="text-muted">No Technicians Found</h5>
                            <p class="text-muted">Try adjusting your search criteria or filters.</p>
                        </div>
                    `);
                    console.log('No results message added');
                }
            }
        } else {
            const existingMessage = document.querySelector('.no-results-message');
            if (existingMessage) {
                existingMessage.remove();
                console.log('No results message removed');
            }
        }
        
        // Update URL without page reload
        let url = new URL(window.location);
        if (search) url.searchParams.set('search', search);
        else url.searchParams.delete('search');
        
        if (shop) url.searchParams.set('shop', shop);
        else url.searchParams.delete('shop');
        
        if (status) url.searchParams.set('status', status);
        else url.searchParams.delete('status');
        
        if (availability) url.searchParams.set('availability', availability);
        else url.searchParams.delete('availability');
        
        // Update URL without reloading page
        window.history.replaceState({}, '', url.toString());
        
        // Debug logging
        console.log('Filter applied:', { search, shop, status, availability });
        console.log('Visible technicians:', totalVisible);
    }
});

function showNotification(message, type) {
    // You can implement your own notification system here
    alert(message);
}
</script>
@endpush
