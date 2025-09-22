<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Auto Repair Shop Admin</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- jQuery (for compatibility) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Custom Admin CSS -->
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    
    @yield('styles')
    
    <!-- Global JavaScript Functions -->
    <script>
    // Apply sidebar state immediately to prevent flicker
    (function() {
        const sidebarState = localStorage.getItem('adminSidebarCollapsed');
        if (sidebarState === 'true') {
            document.documentElement.classList.add('sidebar-collapsed');
        }
        
        // Check if Font Awesome loaded
        setTimeout(function() {
            const headerToggle = document.getElementById('headerSidebarToggle');
            if (headerToggle) {
                const icon = headerToggle.querySelector('.fas.fa-bars');
                if (icon && getComputedStyle(icon, '::before').content !== 'none') {
                    headerToggle.classList.add('fa-loaded');
                } else {
                    headerToggle.classList.remove('fa-loaded');
                }
            }
        }, 1000);
    })();
    
    // Global function for payment method modal - must be defined before onclick handlers
    function showPaymentMethodModal(id, name, imageUrl, roleTypeLabel, roleTypeBadgeClass, accountName, accountNumber, description, isActive, sortOrder, createdAt, updatedAt) {
        console.log('Showing modal for payment method ID:', id);
        
        // Populate modal with payment method data
        document.getElementById('modal-name').textContent = name || 'N/A';
        
        // Handle image with fallback
        const modalImage = document.getElementById('modal-image');
        const modalImageFallback = document.getElementById('modal-image-fallback');
        
        if (modalImage && modalImageFallback) {
            modalImage.style.display = 'inline';
            modalImageFallback.style.display = 'none';
            modalImage.src = imageUrl || '';
            modalImage.alt = name || 'Payment Method Image';
            
            // Handle image error
            modalImage.onerror = function() {
                this.style.display = 'none';
                modalImageFallback.style.display = 'inline';
            };
        }
        
        // Set role type badge
        const roleTypeElement = document.getElementById('modal-role-type');
        if (roleTypeElement) {
            roleTypeElement.textContent = roleTypeLabel || 'N/A';
            roleTypeElement.className = 'badge ' + (roleTypeBadgeClass || 'bg-secondary');
        }
        
        // Set status badge
        const statusElement = document.getElementById('modal-status');
        if (statusElement) {
            statusElement.textContent = isActive ? 'Active' : 'Inactive';
            statusElement.className = 'badge ' + (isActive ? 'bg-success' : 'bg-danger');
        }
        
        // Set other fields
        const sortOrderElement = document.getElementById('modal-sort-order');
        if (sortOrderElement) sortOrderElement.textContent = sortOrder || '0';
        
        const createdAtElement = document.getElementById('modal-created-at');
        if (createdAtElement) createdAtElement.textContent = createdAt || 'N/A';
        
        const updatedAtElement = document.getElementById('modal-updated-at');
        if (updatedAtElement) updatedAtElement.textContent = updatedAt || 'N/A';
        
        // Account details
        const accountNameElement = document.getElementById('modal-account-name');
        if (accountNameElement) accountNameElement.textContent = accountName || 'Not specified';
        
        const accountNumberElement = document.getElementById('modal-account-number');
        if (accountNumberElement) accountNumberElement.textContent = accountNumber || 'Not specified';
        
        const descriptionElement = document.getElementById('modal-description');
        if (descriptionElement) descriptionElement.textContent = description || 'No description available';
        
        // Show modal
        const modalElement = document.getElementById('paymentMethodImageModal');
        if (modalElement && typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
            console.log('Modal should be showing now');
        } else {
            console.error('Bootstrap Modal not available');
            alert('Modal system not available. Payment method: ' + name);
        }
    }
    </script>
</head>
<body class="admin-body">
    <!-- Enhanced Sidebar -->
    <nav class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <div class="brand-icon">
                    <i class="fas fa-screwdriver-wrench"></i>
                </div>
                <div>
                    <div class="brand-title">Auto Repair</div>
                    <div class="brand-subtitle">Admin Panel</div>
                </div>
            </div>
            <button type="button" class="sidebar-minimizer d-md-none" id="sidebarMinimizer" aria-label="Close Sidebar"><i class="fas fa-bars"></i></button>
        </div>
        
        <div class="sidebar-content">
            <div class="sidebar-section">
                <h6 class="sidebar-section-title">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </h6>
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item">
                        <a class="sidebar-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a class="sidebar-nav-link {{ request()->routeIs('admin.appointments.*') ? 'active' : '' }}" href="{{ route('admin.appointments.index') }}">
                            <i class="fas fa-calendar-check"></i>
                            <span>Appointments</span>
                            @php
                                $currentAdmin = auth('admin')->user();
                                $isOwner = $currentAdmin && method_exists($currentAdmin, 'isOwner') ? $currentAdmin->isOwner() : false;
                                $pendingAppointmentsQuery = \App\Models\Appointment::query()->where('status', 'pending');
                                if ($isOwner) {
                                    $ownerShopIds = \App\Models\Shop::where('admin_id', $currentAdmin->id)->pluck('id');
                                    $pendingAppointmentsQuery->whereIn('shop_id', $ownerShopIds);
                                }
                                $pendingAppointmentsCount = $pendingAppointmentsQuery->count();
                            @endphp
                            @if($pendingAppointmentsCount > 0)
                                <span class="notification-badge-sidebar">{{ $pendingAppointmentsCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a class="sidebar-nav-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}" href="{{ route('admin.services.index') }}">
                            <i class="fas fa-tools"></i>
                            <span>Services</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a class="sidebar-nav-link {{ request()->routeIs('admin.shops.*') ? 'active' : '' }}" href="{{ route('admin.shops.index') }}">
                            <i class="fas fa-store"></i>
                            <span>Shop Management</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a class="sidebar-nav-link {{ request()->routeIs('admin.ratings.*') ? 'active' : '' }}" href="{{ route('admin.ratings.index') }}">
                            <i class="fas fa-star"></i>
                            <span>Ratings</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a class="sidebar-nav-link {{ request()->routeIs('admin.payment-methods.*') ? 'active' : '' }}" href="{{ route('admin.payment-methods.index') }}">
                            <i class="fas fa-credit-card"></i>
                            <span>Payment Methods</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a class="sidebar-nav-link {{ request()->routeIs('admin.slot-settings.*') ? 'active' : '' }}" href="{{ route('admin.slot-settings.index') }}">
                            <i class="fas fa-clock"></i>
                            <span>Slot Settings</span>
                        </a>
                    </li>
                    @if(!auth('admin')->user() || !auth('admin')->user()->isOwner())
                    <li class="sidebar-nav-item">
                        <a class="sidebar-nav-link {{ request()->routeIs('admin.feedback.*') ? 'active' : '' }}" href="{{ route('admin.feedback.index') }}">
                            <i class="fas fa-comment-dots"></i>
                            <span>Feedback</span>
                        </a>
                    </li>
                    @endif
                    <li class="sidebar-nav-item">
                        <a class="sidebar-nav-link {{ request()->routeIs('admin.customer-service.*') ? 'active' : '' }}" href="{{ route('admin.customer-service.index') }}">
                            <i class="fas fa-headset"></i>
                            <span>Customer Service</span>
                            @php
                                $admin = auth('admin')->user();
                                $unreadCustomerService = $admin ? \App\Models\CustomerService::where('status', 'open')->where(function($query) use ($admin) {
                                    if ($admin->role !== 'super_admin') {
                                        $query->whereHas('shop', function($q) use ($admin) {
                                            $q->where('admin_id', $admin->id);
                                        });
                                    }
                                })->count() : 0;
                            @endphp
                            @if($unreadCustomerService > 0)
                                <span class="notification-badge-sidebar">{{ $unreadCustomerService }}</span>
                            @endif
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="sidebar-section">
                <h6 class="sidebar-section-title">
                    <i class="fas fa-users-cog me-2"></i>
                    User Management
                </h6>
                <ul class="sidebar-nav">
                    @php $current = auth('admin')->user(); @endphp
                    @if(!$current || !$current->isOwner())
                    <!-- Order: Admin, Owner, User -->
                    <li class="sidebar-nav-item">
                        <a class="sidebar-nav-link {{ (request()->routeIs('admin.admins.*') && request('role') !== 'owner') ? 'active' : '' }}" href="{{ route('admin.admins.index') }}">
                            <i class="fas fa-user-shield"></i>
                            <span>Admin Accounts</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a class="sidebar-nav-link {{ (request()->routeIs('admin.admins.*') && request('role') === 'owner') ? 'active' : '' }}" href="{{ route('admin.admins.index', ['role' => 'owner']) }}">
                            <i class="fas fa-user-tie"></i>
                            <span>Owner Accounts</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a class="sidebar-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                            <i class="fas fa-user"></i>
                            <span>User Accounts</span>
                        </a>
                    </li>
                    @endif
                    <li class="sidebar-nav-item">
                        <a class="sidebar-nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}" href="{{ route('admin.payments.index') }}">
                            <i class="fas fa-money-check-alt"></i>
                            <span>Payment Management</span>
                            @php
                                $currentAdmin = auth('admin')->user();
                                $isOwner = $currentAdmin && method_exists($currentAdmin, 'isOwner') ? $currentAdmin->isOwner() : false;
                                // All unpaid: anything not marked as 'paid' (including null)
                                $unpaidPaymentsQuery = \App\Models\Appointment::query()
                                    ->where(function($q){
                                        $q->where('payment_status', '!=', 'paid')
                                          ->orWhereNull('payment_status');
                                    });
                                if ($isOwner) {
                                    $ownerShopIds = \App\Models\Shop::where('admin_id', $currentAdmin->id)->pluck('id');
                                    $unpaidPaymentsQuery->whereIn('shop_id', $ownerShopIds);
                                }
                                $unpaidPaymentsCount = $unpaidPaymentsQuery->count();
                            @endphp
                            @if($unpaidPaymentsCount > 0)
                                <span class="notification-badge-sidebar">{{ $unpaidPaymentsCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a class="sidebar-nav-link {{ request()->routeIs('admin.payments.history') ? 'active' : '' }}" href="{{ route('admin.payments.history') }}">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span>Payment History</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a class="sidebar-nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}" href="{{ route('admin.notifications.index') }}">
                            <i class="fas fa-bell"></i>
                            <span>Notifications</span>
                            @php
                                $admin = auth('admin')->user();
                                $unreadCount = $admin ? \App\Models\Notification::where('admin_id', $admin->id)->where('is_read', false)->count() : 0;
                            @endphp
                            @if($unreadCount > 0)
                                <span class="notification-badge-sidebar">{{ $unreadCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a class="sidebar-nav-link {{ request()->routeIs('admin.technicians.*') ? 'active' : '' }}" href="{{ route('admin.technicians.index') }}">
                            <i class="fas fa-user-cog"></i>
                            <span>Technicians</span>
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="sidebar-section">
                <h6 class="sidebar-section-title">
                    <i class="fas fa-cog me-2"></i>
                    Settings
                </h6>
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item">
                        <a class="sidebar-nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}">
                            <i class="fas fa-cog"></i>
                            <span>System Settings</span>
                </a>
            </li>
        </ul>
            </div>
        </div>
        
        <div class="sidebar-footer">
            <div class="admin-profile">
                @php $admin = auth('admin')->user(); @endphp
                <div class="profile-avatar">
                    <img src="{{ $admin->profile_picture_url }}" alt="Profile" class="avatar-image">
                </div>
                <div class="profile-info">
                    <h6 class="profile-name">{{ $admin->name }}</h6>
                    <p class="profile-role">{{ (auth('admin')->user()?->isOwner()) ? 'Owner' : 'Administrator' }}</p>
                </div>
            </div>
        </div>
    </nav>

    <!-- Backdrop for mobile sidebar -->
    <div class="sidebar-backdrop d-md-none" id="sidebarBackdrop"></div>

    <!-- Main Content Area -->
    <div class="admin-main" id="adminMain">
        <!-- Enhanced Top Navigation -->
        <header class="admin-header">
            <div class="header-content">
                <div class="header-left">
                    <button class="header-toggle" id="headerSidebarToggle" aria-label="Toggle Sidebar">
                        <i class="fas fa-bars"></i>
                        <!-- Fallback icon in case Font Awesome doesn't load -->
                        <span class="fallback-icon" style="display: none;">☰</span>
                    </button>
                    @php
                        $hideBreadcrumb = request()->routeIs('admin.ratings.*') || request()->routeIs('admin.shops.*');
                    @endphp
                    @if(!$hideBreadcrumb)
                    <div class="breadcrumb-container">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.dashboard') }}">
                                        <i class="fas fa-home"></i>
                                    </a>
                                </li>
                                @yield('breadcrumb')
                            </ol>
                        </nav>
                    </div>
                    @endif
                </div>
                
                <div class="header-right">
                    <div class="header-actions">
                        @php
                            $admin = auth('admin')->user();
                            // Show notifications where current admin is involved (either as recipient or sender)
                            $unreadNotifications = $admin ? \App\Models\Notification::where('admin_id', $admin->id)->where('is_read', false)->latest()->take(10)->get() : collect();
                            $unreadCount = $admin ? \App\Models\Notification::where('admin_id', $admin->id)->where('is_read', false)->count() : 0;
                        @endphp
                        <div class="notification-dropdown me-3">
                            <button class="btn btn-link notification-btn position-relative" data-bs-toggle="dropdown">
                                <i class="fas fa-bell"></i>
                                @if($unreadCount > 0)
                                    <span class="notification-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $unreadCount }}</span>
                                @endif
                            </button>
                            <div class="dropdown-menu dropdown-menu-end notification-menu" style="min-width:320px;">
                                <div class="notification-header">
                                    <h6>Notifications</h6>
                                </div>
                                <div class="notification-scroll-container" style="max-height:400px;overflow-y:auto;overflow-x:hidden;">
                                    @forelse($unreadNotifications as $notification)
                                        @php
                                            $senderName = 'System';
                                            $senderEmail = null;
                                            $senderAvatar = asset('images/default-profile.png');
                                            
                                            // Determine the sender based on notification context
                                            // For admin notifications, admin_id is always the current admin
                                            if ($notification->user_id) {
                                                // Check if this is an incoming notification (user to admin) or outgoing (admin to user)
                                                if ($notification->type === 'feedback' && str_contains($notification->title, 'New Feedback')) {
                                                    // Incoming: User sent feedback to admin
                                                    $userSender = \App\Models\User::find($notification->user_id);
                                                    if ($userSender) {
                                                        $senderName = $userSender->name ?? ($userSender->email ?? 'User');
                                                        $senderEmail = $userSender->email ?? null;
                                                        $senderAvatar = $userSender->profile_picture_url;
                                                    }
                                                } elseif ($notification->type === 'feedback' && str_contains($notification->title, 'Feedback Reply')) {
                                                    // Outgoing: Admin sent feedback reply to user
                                                    $senderName = 'You';
                                                    $senderEmail = $admin->email ?? null;
                                                    $senderAvatar = $admin->profile_picture_url;
                                                } elseif (in_array($notification->type, ['status', 'booking', 'payment'])) {
                                                    // Outgoing: Admin sent status update to user
                                                    $senderName = 'You';
                                                    $senderEmail = $admin->email ?? null;
                                                    $senderAvatar = $admin->profile_picture_url;
                                                } else {
                                                    // Default: User sent something to admin
                                                    $userSender = \App\Models\User::find($notification->user_id);
                                                    if ($userSender) {
                                                        $senderName = $userSender->name ?? ($userSender->email ?? 'User');
                                                        $senderEmail = $userSender->email ?? null;
                                                        $senderAvatar = $userSender->profile_picture_url;
                                                    }
                                                }
                                            } else {
                                                // System notification
                                                $senderName = 'System';
                                                $senderEmail = null;
                                                $senderAvatar = asset('images/default-profile.png');
                                            }
                                        @endphp
                                        <button type="button" class="dropdown-item small notification-item unread admin-bell-notif"
                                           data-title="{{ $notification->title }}"
                                           data-message="{{ $notification->message }}"
                                           data-sender="{{ $senderName }}"
                                           data-sender-email="{{ $senderEmail }}"
                                           data-avatar="{{ $senderAvatar }}"
                                           data-time="{{ $notification->created_at->format('Y-m-d H:i:s') }}"
                                           data-id="{{ $notification->id }}"
                                           data-is-read="{{ $notification->is_read ? '1' : '0' }}">
                                            <div class="d-flex align-items-start" style="gap:0.5rem;">
                                                <img src="{{ $senderAvatar }}" alt="Avatar" style="width:28px;height:28px;border-radius:50%;object-fit:cover;flex-shrink:0;">
                                                <div>
                                                    <div class="fw-semibold text-truncate" style="max-width:200px;">{{ $notification->title }}</div>
                                                    <div class="text-muted small text-truncate" style="max-width:200px;">{{ $notification->message }}</div>
                                                    <div class="text-muted small">{{ $notification->created_at->diffForHumans() }}</div>
                                                </div>
                                            </div>
                                        </button>
                                    @empty
                                        <div class="notification-item">
                                            <div class="notification-content">
                                                <span class="text-muted">No new notifications</span>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                                <div class="notification-footer">
                                    <a href="{{ route('admin.notifications.index') }}" class="dropdown-item text-center">View all notifications</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="admin-dropdown">
                            <button class="btn btn-link admin-profile-btn" data-bs-toggle="dropdown">
                                <div class="profile-avatar-small">
                                    <img src="{{ $admin->profile_picture_url }}" alt="Profile" class="avatar-image-small">
                                </div>
                                <span class="admin-name">{{ $admin->name }}</span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end admin-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.profile') }}">
                                    <i class="fas fa-user me-2"></i> Profile
                                </a>
                            </li>
                                
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('admin.logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="admin-content">
            <div class="content-wrapper">
            @yield('content')
        </div>
        </main>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        // Enhanced sidebar functionality with localStorage persistence
        document.addEventListener('DOMContentLoaded', function() {
            // Safety: remove any duplicated admin headers that might render due to nested layouts
            try {
                const headers = document.querySelectorAll('.admin-header');
                if (headers.length > 1) {
                    for (let i = 1; i < headers.length; i++) {
                        headers[i].parentNode && headers[i].parentNode.removeChild(headers[i]);
                    }
                }
            } catch (_) {}

            const sidebar = document.getElementById('adminSidebar');
            const main = document.getElementById('adminMain');
            const headerSidebarToggle = document.getElementById('headerSidebarToggle');
            const sidebarMinimizer = document.getElementById('sidebarMinimizer');
            const sidebarBackdrop = document.getElementById('sidebarBackdrop');

            const isMobile = () => window.innerWidth < 768;

            function applyMobileIconMode(){
                // Force collapsed icon-only state
                sidebar.classList.add('collapsed');
                sidebar.classList.remove('show');
                document.documentElement.classList.add('sidebar-collapsed');
                if (sidebarBackdrop) sidebarBackdrop.classList.remove('active');
                localStorage.setItem('adminSidebarCollapsed','true');
                // Disable hamburger
                if (headerSidebarToggle) headerSidebarToggle.onclick = null;
            }

            function removeMobileIconMode(){
                document.documentElement.classList.remove('sidebar-open-mobile');
                if (headerSidebarToggle) headerSidebarToggle.onclick = headerToggleHandler;
            }

            // Load initial state
            const sidebarState = localStorage.getItem('adminSidebarCollapsed');
            const isCollapsed = sidebarState === 'true';
            if (isMobile()) {
                applyMobileIconMode();
            } else {
            if (isCollapsed) {
                sidebar.classList.add('collapsed');
                main.classList.add('expanded');
                document.documentElement.classList.add('sidebar-collapsed');
            } else {
                sidebar.classList.remove('collapsed');
                main.classList.remove('expanded');
                document.documentElement.classList.remove('sidebar-collapsed');
                }
            }

            // Unified desktop toggle
            function toggleSidebarDesktop(){
                const collapsed = sidebar.classList.contains('collapsed');
                if (collapsed) {
                    sidebar.classList.remove('collapsed');
                    main.classList.remove('expanded');
                    document.documentElement.classList.remove('sidebar-collapsed');
                    localStorage.setItem('adminSidebarCollapsed','false');
                } else {
                    sidebar.classList.add('collapsed');
                    main.classList.add('expanded');
                    document.documentElement.classList.add('sidebar-collapsed');
                    localStorage.setItem('adminSidebarCollapsed','true');
                }
            }

            function headerToggleHandler(e){
                e && e.preventDefault();
                if (!isMobile()) toggleSidebarDesktop();
            }

                    if (headerSidebarToggle) {
                headerSidebarToggle.onclick = headerToggleHandler;
            }

            // Adapt on resize
            window.addEventListener('resize', function(){
                if (isMobile()) {
                    applyMobileIconMode();
                            } else {
                    removeMobileIconMode();
                }
            });
            
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        function showImageModal(src) {
            const modalImg = document.getElementById('modalProfileImg');
            modalImg.src = src;
            const modal = new bootstrap.Modal(document.getElementById('profileImageModal'));
            modal.show();
        }
    </script>
    
    @stack('scripts')

    <!-- Modal for image zoom -->
    <div class="modal fade" id="profileImageModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0">
          <div class="modal-body p-0 text-center">
            <img id="modalProfileImg" src="" alt="Profile" style="max-width:90vw;max-height:80vh;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.2);">
          </div>
        </div>
      </div>
    </div>

    <!-- Modal for notification details -->
    <div class="modal fade" id="notificationDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalNotificationTitle">Notification Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="d-flex flex-column align-items-center mb-3">
                        <img id="modalNotificationAvatar" src="{{ asset('images/default-profile.png') }}"
                             alt="Sender Avatar"
                             style="width:90px;height:90px;border-radius:50%;object-fit:cover;box-shadow:0 2px 12px rgba(44,62,80,0.10);margin-bottom:1rem;">
                        <div class="fw-bold fs-5" id="modalNotificationSender">Sender Name</div>
                        <div class="text-muted small" id="modalNotificationSenderEmail"></div>
                    </div>
                    <div class="mb-3">
                        <h6 id="modalNotificationTitleText" class="fw-semibold"></h6>
                        <div id="modalNotificationMessage" class="alert alert-light border rounded-3 py-3 px-4 text-start" style="min-height:60px;"></div>
                        <div id="downloadReceiptSection" class="mt-3" style="display: none;">
                            <a id="downloadReceiptBtn" href="#" class="btn btn-success btn-sm" target="_blank">
                                <i class="fas fa-download me-2"></i>Download Receipt
                            </a>
                        </div>
                    </div>
                    <div class="mb-2 text-muted"><strong>Time:</strong> <span id="modalNotificationTime"></span></div>
                    <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="toggleReadModalBtn">Mark as Read</button>
                </div>
            </div>
        </div>
    </div>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    let currentNotificationId = null;
    let currentIsRead = null;
    document.body.addEventListener('click', function(e) {
        const target = e.target.closest('.admin-bell-notif');
        if (target) {
            document.getElementById('modalNotificationTitle').textContent = 'Notification Details';
            document.getElementById('modalNotificationTitleText').textContent = target.getAttribute('data-title') || 'Notification';
            document.getElementById('modalNotificationMessage').textContent = target.getAttribute('data-message') || '';
            document.getElementById('modalNotificationSender').textContent = target.getAttribute('data-sender') || 'System';
            document.getElementById('modalNotificationSenderEmail').textContent = target.getAttribute('data-sender-email') || '';
            const avatar = target.getAttribute('data-avatar');
            const avatarEl = document.getElementById('modalNotificationAvatar');
            if (avatar) avatarEl.src = avatar;
            document.getElementById('modalNotificationTime').textContent = target.getAttribute('data-time');
            currentNotificationId = target.getAttribute('data-id');
            currentIsRead = target.getAttribute('data-is-read') === '1';
            const toggleBtn = document.getElementById('toggleReadModalBtn');
            toggleBtn.textContent = currentIsRead ? 'Mark as Unread' : 'Mark as Read';
            const modal = new bootstrap.Modal(document.getElementById('notificationDetailsModal'));
            modal.show();
        }
    });
    const toggleReadModalBtn = document.getElementById('toggleReadModalBtn');
    if (toggleReadModalBtn) {
        toggleReadModalBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (!currentNotificationId) return;
            fetch(`/admin/notifications/toggle-read/${currentNotificationId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    currentIsRead = !currentIsRead;
                    this.textContent = currentIsRead ? 'Mark as Unread' : 'Mark as Read';
                    const item = document.querySelector(`.admin-bell-notif[data-id="${currentNotificationId}"]`);
                    if (item) item.setAttribute('data-is-read', currentIsRead ? '1' : '0');
                }
            }).catch(() => {});
        });
    }
});
    </script>
</body>
</html> 

<style>
/* Admin Body */
.admin-body {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
}

/* Immediate sidebar state application */
html.sidebar-collapsed .admin-sidebar {
    width: 70px !important;
}

html.sidebar-collapsed .admin-main {
    margin-left: 70px !important;
}

/* Enhanced Sidebar */
.admin-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 280px;
    background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
    color: white;
    z-index: 1000;
    transition: width 0.3s ease;
    box-shadow: 4px 0 15px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transform: none !important;
}

.admin-sidebar.collapsed {
    width: 70px !important;
}

/* Sidebar Header */
.sidebar-header {
    padding: 1.5rem;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    display: flex;
    align-items: center;
    justify-content: space-between; /* Added for minimizer button */
    flex-shrink: 0;
}

.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.brand-icon {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    flex-shrink: 0;
}

.brand-content {
    flex: 1;
    min-width: 0;
}

.brand-title {
    font-size: 1.2rem;
    font-weight: 700;
    margin: 0;
    color: white;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.brand-subtitle {
    font-size: 0.8rem;
    margin: 0;
    opacity: 0.7;
    color: #bdc3c7;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Sidebar Content */
.sidebar-content {
    flex: 1;
    padding: 1.5rem 0;
    overflow-y: auto;
    overflow-x: hidden;
}

.sidebar-section {
    margin-bottom: 2rem;
}

.sidebar-section-title {
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #bdc3c7;
    margin: 0 1.5rem 1rem 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    white-space: nowrap;
}

.sidebar-nav {
    list-style: none;
    padding: 0;
    margin: 0;
}

/* Prevent any movement on sidebar items */
.sidebar-nav-item {
    margin: 0.25rem 0;
    position: relative;
}

.sidebar-nav-link {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem 1.5rem;
    color: #ecf0f1;
    text-decoration: none;
    transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
    border-radius: 0 25px 25px 0;
    margin-right: 1rem;
    position: relative;
    white-space: nowrap;
    border: 2px solid transparent;
    transform: none !important;
}

.sidebar-nav-link:hover {
    background: rgba(255,255,255,0.1);
    color: white;
    text-decoration: none;
    transform: none !important;
}

.sidebar-nav-link.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(102,126,234,0.3);
    border-color: rgba(255,255,255,0.2);
    transform: none !important;
}

.sidebar-nav-link i {
    width: 20px;
    text-align: center;
    font-size: 1rem;
    flex-shrink: 0;
    transition: none;
}

.sidebar-nav-link span {
    font-weight: 500;
    font-size: 0.95rem;
    flex: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: none;
}

/* Collapsed Sidebar */
.admin-sidebar.collapsed .brand-content,
.admin-sidebar.collapsed .sidebar-section-title,
.admin-sidebar.collapsed .sidebar-nav-link span {
    display: none;
}

.admin-sidebar.collapsed .sidebar-nav-link {
    justify-content: center;
    padding: 0.75rem;
    margin-right: 0.5rem;
    border-radius: 12px;
    border: 2px solid transparent;
}

.admin-sidebar.collapsed .sidebar-nav-link.active {
    border-color: rgba(255,255,255,0.3);
}

.admin-sidebar.collapsed .sidebar-nav-link i {
    margin: 0;
}

/* Sidebar Footer */
.sidebar-footer {
    padding: 1.5rem;
    border-top: 1px solid rgba(255,255,255,0.1);
    flex-shrink: 0;
}

.admin-profile {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.profile-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
}

.avatar-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

.profile-info {
    flex: 1;
    min-width: 0;
}

.profile-name {
    font-size: 0.95rem;
    font-weight: 600;
    margin: 0;
    color: white;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.profile-role {
    font-size: 0.8rem;
    margin: 0;
    opacity: 0.7;
    color: #bdc3c7;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Collapsed Sidebar Profile */
.admin-sidebar.collapsed .profile-info {
    display: none;
}

.admin-sidebar.collapsed .admin-profile {
    justify-content: center;
    gap: 0;
}

.admin-sidebar.collapsed .profile-avatar {
    width: 40px;
    height: 40px;
}

.admin-sidebar.collapsed .sidebar-footer {
    padding: 1rem 0.5rem;
    text-align: center;
}

/* Mobile Profile Enhancements */
@media (max-width: 768px) {
    .admin-sidebar .admin-profile {
        position: relative;
        overflow: hidden;
    }
    
    .admin-sidebar .admin-profile::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0.05));
        pointer-events: none;
    }
    
    .admin-sidebar .profile-info {
        position: relative;
        z-index: 1;
    }
    
    .admin-sidebar .profile-avatar {
        position: relative;
        z-index: 1;
        border: 2px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
}

/* Main Content Area */
.admin-main {
    margin-left: 280px;
    transition: margin-left 0.3s ease;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.admin-main.expanded {
    margin-left: 70px !important;
}

/* Enhanced Header */
.admin-header {
    background: white;
    border-bottom: 1px solid #e9ecef;
    padding: 1rem 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 100;
    flex-shrink: 0;
}

/* Safety: if a second header accidentally renders, hide any after the first */
.admin-header:not(:first-of-type) { display: none !important; }

.header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 2rem;
    flex: 1;
}

.header-toggle {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    color: #495057;
    font-size: 1.2rem;
    cursor: pointer;
    padding: 0.75rem;
    border-radius: 10px;
    transition: all 0.3s ease;
    flex-shrink: 0;
    min-width: 48px;
    min-height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.header-toggle:hover {
    background: #e9ecef;
    color: #2c3e50;
    border-color: #dee2e6;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateY(-1px);
}

.header-toggle:active {
    transform: translateY(0);
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

.header-toggle i {
    font-size: 1.1rem;
    font-weight: 600;
}

.breadcrumb-container {
    display: flex;
    align-items: center;
    flex: 1;
}

.breadcrumb {
    background: none;
    padding: 0;
    margin: 0;
}

.breadcrumb-item a {
    color: #6c757d;
    text-decoration: none;
    transition: color 0.3s ease;
}

.breadcrumb-item a:hover {
    color: #495057;
}

.breadcrumb-item.active {
    color: #495057;
    font-weight: 600;
}

/* Header Right */
.header-right {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-shrink: 0;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
}

/* Notification Dropdown */
.notification-dropdown {
    position: relative;
}

.notification-btn {
    position: relative;
    background: none;
    border: none;
    color: #6c757d;
    font-size: 1.2rem;
    padding: 0.5rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.notification-btn:hover {
    background: #f8f9fa;
    color: #495057;
}

.notification-badge {
    background-color: #dc3545 !important;
    color: #fff !important;
    min-width: 22px;
    height: 22px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    line-height: 1;
    padding: 0 6px;
    border: 2px solid #fff; /* ring to increase contrast */
}

.notification-menu {
    width: 350px;
    padding: 0;
    border: none;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.notification-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notification-header h6 {
    margin: 0;
    font-weight: 600;
    color: #2c3e50;
}

.mark-all-read {
    font-size: 0.85rem;
    color: #007bff;
    text-decoration: none;
}

.notification-list {
    max-height: 300px;
    overflow-y: auto;
}

.notification-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f8f9fa;
    transition: background-color 0.3s ease;
}

.notification-item:hover {
    background: #f8f9fa;
}

.notification-item.unread {
    background: #f0f8ff;
}

.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.notification-content {
    flex: 1;
    min-width: 0;
}

.notification-text {
    margin: 0 0 0.25rem 0;
    font-size: 0.9rem;
    color: #2c3e50;
    line-height: 1.4;
}

.notification-time {
    font-size: 0.8rem;
    color: #6c757d;
}

.notification-footer {
    padding: 1rem 1.5rem;
    text-align: center;
    border-top: 1px solid #e9ecef;
}

.view-all-notifications {
    color: #007bff;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
}

/* Admin Dropdown */
.admin-dropdown {
    position: relative;
}

.admin-profile-btn {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: none;
    border: none;
    color: #6c757d;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.admin-profile-btn:hover {
    background: #f8f9fa;
    color: #495057;
}

.profile-avatar-small {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
}

.avatar-image-small {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder-small {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
}

.admin-name {
    font-weight: 500;
    color: #2c3e50;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 150px;
}

.admin-menu {
    min-width: 200px;
    border: none;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    padding: 0.5rem 0;
}

.admin-menu .dropdown-item {
    padding: 0.75rem 1.5rem;
    color: #2c3e50;
    transition: all 0.3s ease;
}

.admin-menu .dropdown-item:hover {
    background: #f8f9fa;
    color: #495057;
}

.admin-menu .dropdown-item.text-danger:hover {
    background: #f8d7da;
    color: #721c24;
}

/* Content Area */
.admin-content {
    flex: 1;
    padding: 2rem;
    overflow-x: auto;
}

.content-wrapper {
    max-width: 1400px;
    margin: 0 auto;
}

/* Responsive Design */
@media (max-width: 768px) {
    .admin-sidebar {
        transform: translateX(-100%);
        width: 280px;
        transition: transform 0.3s ease;
    }
    
    .admin-sidebar.show {
        transform: translateX(0);
    }
    
    .admin-sidebar.collapsed {
        width: 70px !important;
        transform: translateX(-100%);
    }
    
    .admin-sidebar.collapsed.show {
        transform: translateX(0);
    }
    
    .admin-main {
        margin-left: 0;
        transition: margin-left 0.3s ease;
    }
    
    .admin-main.expanded {
        margin-left: 0;
    }
    
    /* Mobile overlay for sidebar */
    .admin-sidebar::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: -1;
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
    }
    
    .admin-sidebar.show::before {
        opacity: 1;
        pointer-events: auto;
    }
    
    .header-content {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .header-left {
        width: 100%;
        justify-content: space-between;
    }
    
    .header-right {
        width: 100%;
        justify-content: flex-end;
    }
    
    .notification-menu {
        width: 300px;
    }
    
    .admin-content {
        padding: 1rem;
    }

    /* Make header sticky for better usability */
    .admin-header {
        position: sticky;
        top: 0;
        z-index: 1020;
    }

    /* Full-width dropdowns within header on mobile */
    .notification-dropdown .dropdown-menu,
    .admin-dropdown .dropdown-menu {
        width: calc(100vw - 2rem);
        max-width: 100%;
        left: 1rem !important;
        right: 1rem !important;
        transform: none !important;
    }

    /* Tables: allow horizontal scroll without breaking layout */
    .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    table.table { font-size: 0.9rem; }
    table.table th, table.table td { white-space: nowrap; }

    /* Cards and buttons more compact */
    .card { margin-bottom: 1rem; }
    .card-body { padding: 0.75rem; }
    .btn { padding: 0.5rem 0.75rem; font-size: 0.9rem; }
    .btn-sm { padding: 0.35rem 0.5rem; font-size: 0.8rem; }

    /* Sidebar toggle bigger hit area and always visible */
    .header-toggle { 
        padding: 0.75rem; 
        font-size: 1.2rem;
        background: #667eea;
        border: 2px solid #5a6fd8;
        border-radius: 12px;
        min-width: 52px;
        min-height: 52px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        position: relative;
        z-index: 1030;
    }
    
    .header-toggle:hover {
        background: #5a6fd8;
        border-color: #4a5fc8;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }
    
    .header-toggle:active {
        transform: translateY(0);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    
    .header-toggle i {
        font-size: 1.3rem;
        font-weight: 700;
        /* Ensure icon is always visible */
        color: white !important;
        text-shadow: 0 1px 3px rgba(0,0,0,0.3);
    }
    
    /* Make the three lines more prominent */
    .header-toggle .fas.fa-bars {
        font-size: 1.4rem;
        letter-spacing: -1px;
    }
    
    /* Fallback icon styling */
    .fallback-icon {
        font-size: 1.5rem;
        font-weight: bold;
        color: white !important;
    }
    
    /* Show fallback icon if Font Awesome fails */
    .header-toggle:not(.fa-loaded) .fas.fa-bars {
        display: none;
    }
    
    .header-toggle:not(.fa-loaded) .fallback-icon {
        display: inline !important;
    }

    /* Breadcrumbs wrap nicely */
    .breadcrumb { flex-wrap: wrap; margin-bottom: 0; }
    .breadcrumb-item { white-space: nowrap; }
    
    /* Mobile profile adjustments */
    .admin-sidebar .admin-profile {
        padding: 1rem;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.1);
        margin: 0 0.5rem 0.5rem 0.5rem;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .admin-sidebar.collapsed .admin-profile {
        margin: 0 0.25rem 0.25rem 0.25rem;
        padding: 0.5rem;
    }
    
    /* Mobile sidebar content adjustments */
    .sidebar-content {
        padding: 1rem 0;
    }
    
    .sidebar-section {
        margin-bottom: 1.5rem;
    }
    
    .sidebar-nav-link {
        margin-right: 0.5rem;
        border-radius: 0 20px 20px 0;
        font-size: 0.95rem;
        padding: 0.875rem 1.25rem;
    }
    
    .admin-sidebar.collapsed .sidebar-nav-link {
        margin-right: 0.25rem;
        border-radius: 12px;
        padding: 0.75rem;
    }
    
    /* Mobile sidebar header adjustments */
    .sidebar-header {
        padding: 1.25rem;
    }
    
    .brand-icon {
        width: 40px;
        height: 40px;
        font-size: 1.25rem;
    }
    
    .brand-title {
        font-size: 1.1rem;
    }
    
    .brand-subtitle {
        font-size: 0.75rem;
    }
    
    /* Mobile sidebar footer adjustments */
    .sidebar-footer {
        padding: 1.25rem;
    }
    
    .profile-avatar {
        width: 40px;
        height: 40px;
    }
    
    .profile-name {
        font-size: 0.9rem;
    }
    
    .profile-role {
        font-size: 0.75rem;
    }
    
    /* Mobile notification badge adjustments */
    .notification-badge-sidebar {
        width: 16px;
        height: 16px;
        font-size: 0.65rem;
    }
    
    /* Mobile sidebar section titles */
    .sidebar-section-title {
        font-size: 0.75rem;
        margin: 0 1.25rem 0.875rem 1.25rem;
        padding-bottom: 0.375rem;
    }
    
    /* Mobile touch improvements */
    .sidebar-nav-link {
        min-height: 48px;
        touch-action: manipulation;
    }
    
    .header-toggle {
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
        /* Ensure toggle is always visible on mobile */
        position: relative !important;
        z-index: 1030 !important;
        /* Mobile-specific styling */
        background: #667eea !important;
        color: white !important;
        border-color: #5a6fd8 !important;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4) !important;
        /* Debug outline - remove this after testing */
        outline: 3px solid red !important;
        outline-offset: 2px !important;
        /* Force display */
        display: flex !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    /* Ensure header toggle is always above other elements on mobile */
    .admin-header {
        position: relative;
        z-index: 1020;
        /* Debug outline */
        outline: 2px solid blue !important;
    }
    
    .header-left {
        position: relative;
        z-index: 1025;
        /* Debug outline */
        outline: 2px solid green !important;
        /* Ensure proper display */
        display: flex !important;
        align-items: center !important;
        gap: 1rem !important;
        width: auto !important;
        min-width: 0 !important;
        overflow: visible !important;
    }
    
    /* Force header toggle to be visible */
    .header-left .header-toggle {
        position: static !important;
        float: none !important;
        clear: none !important;
        margin: 0 !important;
        padding: 0.75rem !important;
        width: 52px !important;
        height: 52px !important;
        min-width: 52px !important;
        min-height: 52px !important;
        flex-shrink: 0 !important;
        order: -1 !important;
        /* Additional visibility rules */
        transform: none !important;
        filter: none !important;
        backdrop-filter: none !important;
        /* Ensure it's not clipped */
        clip: auto !important;
        clip-path: none !important;
        /* Force display */
        display: flex !important;
        visibility: visible !important;
        opacity: 1 !important;
        /* Ensure proper stacking */
        z-index: 1030 !important;
    }
    
    /* Override any conflicting styles */
    .header-toggle,
    .header-toggle *,
    .header-toggle::before,
    .header-toggle::after {
        box-sizing: border-box !important;
        display: inherit !important;
    }
    
    /* Ensure toggle button is always visible even when sidebar is open */
    .header-toggle {
        position: relative !important;
        z-index: 1030 !important;
        /* Force visibility */
        opacity: 1 !important;
        visibility: visible !important;
        display: flex !important;
        /* Force positioning */
        position: static !important;
        float: none !important;
        clear: none !important;
        /* Ensure it's not hidden by any parent */
        overflow: visible !important;
        clip: auto !important;
        clip-path: none !important;
        /* Mobile-specific positioning */
        margin: 0 !important;
        padding: 0.75rem !important;
        width: auto !important;
        height: auto !important;
        min-width: 52px !important;
        min-height: 52px !important;
    }
    
    /* Add a subtle glow effect to make it stand out */
    .header-toggle::before {
        content: '';
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        background: linear-gradient(45deg, #667eea, #764ba2);
        border-radius: 14px;
        z-index: -1;
        opacity: 0.3;
        animation: pulse 2s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 0.3; transform: scale(1); }
        50% { opacity: 0.6; transform: scale(1.05); }
    }
    
    /* Mobile scroll improvements */
    .sidebar-content {
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    
    .sidebar-content::-webkit-scrollbar {
        display: none;
    }
    
    /* Mobile overlay improvements */
    .admin-sidebar::before {
        backdrop-filter: blur(5px);
    }
}

/* Animation */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.notification-item {
    animation: slideIn 0.3s ease-out;
}

/* Scrollbar Styling */
.sidebar-content::-webkit-scrollbar {
    width: 6px;
}

.sidebar-content::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.1);
}

.sidebar-content::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.3);
    border-radius: 3px;
}

.sidebar-content::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.5);
}

/* Ensure proper z-index stacking */
.admin-sidebar {
    z-index: 1030;
}

.admin-header {
    z-index: 1020;
}

.notification-dropdown .dropdown-menu,
.admin-dropdown .dropdown-menu {
    z-index: 1040;
}

        /* Notification dropdown scroll styling */
        .notification-scroll-container {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e0 #f1f5f9;
        }
        .notification-scroll-container::-webkit-scrollbar {
            width: 6px;
        }
        .notification-scroll-container::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }
        .notification-scroll-container::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 3px;
        }
        .notification-scroll-container::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Sidebar notification badge */
        .notification-badge-sidebar {
            position: absolute;
            top: 0;
            right: 0;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            transform: translate(50%, -50%);
        }

        .admin-sidebar.collapsed .notification-badge-sidebar {
            right: 50%;
            transform: translate(50%, -50%);
        }

/* Base sidebar sizing so JS translate matches */
.admin-sidebar { width: 280px; }

/* Main Content Area */
.admin-main {
    margin-left: 280px;
    transition: margin-left 0.3s ease;
    min-height: 100vh;
}

/* Mobile sidebar helpers */
@media (max-width: 767.98px) {
    /* Permanently collapsed icon-only sidebar on mobile */
    .admin-sidebar {
        position: fixed; top: 0; left: 0; height: 100vh;
        width: 70px !important;
        transform: none !important;
        transition: none;
        z-index: 1051;
    }
    .admin-main { margin-left: 70px !important; }
    /* Hide text labels in sidebar */
    .admin-sidebar .brand-content,
    .admin-sidebar .sidebar-section-title,
    .admin-sidebar .sidebar-nav-link span,
    .admin-sidebar .profile-info { display: none !important; }
    .admin-sidebar .sidebar-nav-link { justify-content: center; }

    /* Hide both header hamburger and in-sidebar minimizer */
    .admin-header .header-toggle,
    .sidebar-minimizer { display: none !important; }

    /* Backdrop not used in this mode */
    .sidebar-backdrop { display: none !important; }
}

/* Admin mobile tables & content spacing */
@media (max-width: 767.98px) {
    .admin-content { padding: 0.75rem; }
    .admin-content .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .admin-content .table { font-size: 0.875rem; }
    .admin-content .table th,
    .admin-content .table td { padding: 0.5rem 0.5rem; vertical-align: middle; }
    .admin-content .card { border-radius: 12px; }
        }
</style> 