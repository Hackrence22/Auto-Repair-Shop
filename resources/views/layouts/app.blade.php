<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Auto Repair Shop')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">

    @stack('styles')

    @php
    use Illuminate\Support\Facades\Storage;
    @endphp
</head>
<body>
    <div class="page-wrapper">
        <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                    <i class="fas fa-tools me-2"></i>
                    <span class="brand-text">Auto Repair Shop</span>
                </a>
                <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto align-items-center">
                        @guest
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}" href="{{ route('login') }}">
                                    <i class="fas fa-sign-in-alt me-1"></i> Login
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('register') ? 'active' : '' }}" href="{{ route('register') }}">
                                    <i class="fas fa-user-plus me-1"></i> Register
                                </a>
                            </li>
                        @else
                            <!-- Desktop Navigation Order -->
                            <li class="nav-item me-3 d-none d-lg-block">
                                <a class="nav-link {{ request()->routeIs('shops.*') ? 'active' : '' }}" href="{{ route('shops.index') }}">
                                    <i class="fas fa-store me-1"></i> Our Shops
                                </a>
                            </li>
                            <li class="nav-item me-3 d-none d-lg-block">
                                <a class="nav-link {{ request()->routeIs('appointments.index') ? 'active' : '' }}" href="{{ route('appointments.index') }}">
                                    <i class="fas fa-calendar-alt me-1"></i> My Appointments
                                </a>
                            </li>
                            <li class="nav-item me-3 d-none d-lg-block">
                                <a class="nav-link {{ request()->routeIs('appointments.history') ? 'active' : '' }}" href="{{ route('appointments.history') }}">
                                    <i class="fas fa-file-invoice-dollar me-1"></i> Payment History
                                </a>
                            </li>
                            
                            @auth
                                @php
                                    $recentNotifications = \App\Models\Notification::where('user_id', Auth::id())->latest()->take(10)->get();
                                    $unreadCount = \App\Models\Notification::where('user_id', Auth::id())->where('is_read', false)->count();
                                @endphp
                                <li class="nav-item dropdown me-2 d-none d-lg-block">
                                    <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-bell"></i>
                                        @if($unreadCount > 0)
                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="transform: translate(-50%, -50%);">{{ $unreadCount }}</span>
                                        @endif
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" style="min-width:260px;">
                                        <li class="dropdown-header fw-bold">Notifications</li>
                                        <div class="notification-scroll-container" style="max-height:300px;overflow-y:auto;overflow-x:hidden;">
                                            @forelse($recentNotifications as $notification)
                                                @php
                                                    $sender = null;
                                                    $senderName = 'System';
                                                    $senderEmail = null;
                                                    $senderAvatar = asset('images/default-profile.png');
                                                    if ($notification->admin_id) {
                                                        $sender = \App\Models\Admin::find($notification->admin_id);
                                                        if ($sender) {
                                                            $senderName = $sender->name ?? ($sender->email ?? 'Admin');
                                                            $senderEmail = $sender->email ?? null;
                                                            $avatarPath = $sender->profile_picture ?? null;
                                                            if ($avatarPath) {
                                                                $senderAvatar = Storage::disk('public')->url($avatarPath);
                                                            }
                                                        }
                                                    } elseif ($notification->user_id) {
                                                        $sender = \App\Models\User::find($notification->user_id);
                                                        if ($sender) {
                                                            $senderName = $sender->name ?? ($sender->email ?? 'User');
                                                            $senderEmail = $sender->email ?? null;
                                                            $avatarPath = $sender->profile_picture ?? null;
                                                        if ($avatarPath) {
                                                            $senderAvatar = Storage::disk('public')->url($avatarPath);
                                                        }
                                                        }
                                                    }
                                                    $isRead = $notification->is_read;
                                                @endphp
                                                <li class="notification-bell-item position-relative mb-2" style="background:#f8fafd;border-radius:12px;box-shadow:0 1px 4px rgba(44,62,80,0.04);min-height:48px;">
                                                    <a href="#" class="dropdown-item notification-item p-3 pe-5" style="font-size:0.97rem;min-width:0;background:none;border-radius:12px;transition:background 0.15s;" data-title="{{ $notification->title }}" data-message="{{ $notification->message }}" data-sender="{{ $senderName }}" data-sender-email="{{ $senderEmail }}" data-avatar="{{ $senderAvatar }}" data-time="{{ $notification->created_at->format('Y-m-d H:i:s') }}" data-id="{{ $notification->id }}" data-is-read="{{ $isRead ? '1' : '0' }}">
                                                        <div class="d-flex align-items-start" style="gap:0.5rem;">
                                                            <img src="{{ $senderAvatar }}" alt="Avatar" style="width:28px;height:28px;border-radius:50%;object-fit:cover;flex-shrink:0;">
                                                            <div>
                                                                <div class="fw-semibold text-truncate" style="max-width:180px;">{{ $notification->title }}</div>
                                                                <div class="text-muted text-truncate" style="max-width:180px;">{{ $notification->message }}</div>
                                                                <div class="text-muted small mt-1">{{ $notification->created_at->diffForHumans() }}</div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                            @empty
                                                <li><span class="dropdown-item text-muted">No notifications</span></li>
                                            @endforelse
                                        </div>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a href="{{ route('notifications.index') }}" class="dropdown-item text-center">View all notifications</a></li>
                                    </ul>
                                </li>
                            @endauth
                            @auth
                            <li class="nav-item dropdown d-none d-lg-block">
                                <a class="nav-link dropdown-toggle d-flex align-items-center {{ request()->routeIs('profile.*') ? 'active' : '' }}" 
                                   href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                    <div class="nav-profile-container me-2">
                                        <img src="{{ Auth::user()->profile_picture_url }}" alt="Profile" class="nav-profile-picture" style="width:35px;height:35px;border-radius:50%;object-fit:cover;display:block;flex-shrink:0;">
                                    </div>
                                    <span class="nav-username">{{ Auth::user()->name }}</span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('profile.show') ? 'active' : '' }}" href="{{ route('profile.show') }}">
                                            <i class="fas fa-user me-2"></i> My Profile
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('customer-service.*') ? 'active' : '' }}" href="{{ route('customer-service.index') }}">
                                            <i class="fas fa-headset me-2"></i> Customer Service
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}" class="p-0 m-0">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </li>

                            <!-- Mobile Navigation Order (User Profile First, Notification Bell Last) -->
                            <li class="nav-item dropdown d-lg-none mobile-user-profile">
                                <a class="nav-link dropdown-toggle d-flex align-items-center {{ request()->routeIs('profile.*') ? 'active' : '' }}" 
                                   href="#" id="mobileUserDropdown" role="button" data-bs-toggle="dropdown">
                                    <div class="nav-profile-container me-2">
                                        <img src="{{ Auth::user()->profile_picture_url }}" alt="Profile" class="nav-profile-picture" style="width:40px;height:40px;border-radius:50%;object-fit:cover;display:block;flex-shrink:0;">
                                    </div>
                                    <span class="nav-username">{{ Auth::user()->name }}</span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('profile.show') ? 'active' : '' }}" href="{{ route('profile.show') }}">
                                            <i class="fas fa-user me-2"></i> My Profile
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('customer-service.*') ? 'active' : '' }}" href="{{ route('customer-service.index') }}">
                                            <i class="fas fa-headset me-2"></i> Customer Service
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}" class="p-0 m-0">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item d-lg-none">
                                <a class="nav-link {{ request()->routeIs('shops.*') ? 'active' : '' }}" href="{{ route('shops.index') }}">
                                    <i class="fas fa-store me-1"></i> Our Shops
                                </a>
                            </li>
                            <li class="nav-item d-lg-none">
                                <a class="nav-link {{ request()->routeIs('appointments.index') ? 'active' : '' }}" href="{{ route('appointments.index') }}">
                                    <i class="fas fa-calendar-alt me-1"></i> My Appointments
                                </a>
                            </li>
                            <li class="nav-item d-lg-none">
                                <a class="nav-link {{ request()->routeIs('appointments.history') ? 'active' : '' }}" href="{{ route('appointments.history') }}">
                                    <i class="fas fa-file-invoice-dollar me-1"></i> Payment History
                                </a>
                            </li>
                            @endauth
                            
                            @auth
                                <li class="nav-item dropdown d-lg-none mobile-notification-bell">
                                    <a class="nav-link position-relative" href="#" id="mobileNotificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span>Notification Bell</span>
                                        <div class="position-relative d-inline-block ms-1">
                                            <i class="fas fa-bell"></i>
                                            @if($unreadCount > 0)
                                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="transform: translate(-50%, -50%); font-size: 0.75rem;">{{ $unreadCount }}</span>
                                            @endif
                                        </div>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="mobileNotificationDropdown" style="min-width:260px;">
                                        <li class="dropdown-header fw-bold">Notifications</li>
                                        @forelse($recentNotifications as $notification)
                                            @php
                                                $sender = null;
                                                $senderName = 'System';
                                                $senderEmail = null;
                                                $senderAvatar = asset('images/default-profile.png');
                                                if ($notification->admin_id) {
                                                    $sender = \App\Models\Admin::find($notification->admin_id);
                                                    if ($sender) {
                                                        $senderName = $sender->name ?? ($sender->email ?? 'Admin');
                                                        $senderEmail = $sender->email ?? null;
                                                        $avatarPath = $sender->profile_picture ?? null;
                                                        if ($avatarPath && Storage::disk('public')->exists($avatarPath)) {
                                                            $senderAvatar = Storage::disk('public')->url($avatarPath);
                                                        }
                                                    }
                                                } elseif ($notification->user_id) {
                                                    $sender = \App\Models\User::find($notification->user_id);
                                                    if ($sender) {
                                                        $senderName = $sender->name ?? ($sender->email ?? 'User');
                                                        $senderEmail = $sender->email ?? null;
                                                        $avatarPath = $sender->profile_picture ?? null;
                                                        if ($avatarPath && Storage::disk('public')->exists($avatarPath)) {
                                                            $senderAvatar = Storage::disk('public')->url($avatarPath);
                                                        }
                                                    }
                                                }
                                                $isRead = $notification->is_read;
                                            @endphp
                                            <li class="notification-bell-item position-relative mb-2" style="background:#f8fafd;border-radius:12px;box-shadow:0 1px 4px rgba(44,62,80,0.04);min-height:48px;">
                                                <a href="#" class="dropdown-item notification-item p-3 pe-5" style="font-size:0.97rem;min-width:0;background:none;border-radius:12px;transition:background 0.15s;" data-title="{{ $notification->title }}" data-message="{{ $notification->message }}" data-sender="{{ $senderName }}" data-sender-email="{{ $senderEmail }}" data-avatar="{{ $senderAvatar }}" data-time="{{ $notification->created_at->format('Y-m-d H:i:s') }}" data-id="{{ $notification->id }}" data-is-read="{{ $isRead ? '1' : '0' }}">
                                                    <div class="d-flex align-items-start" style="gap:0.5rem;">
                                                        <img src="{{ $senderAvatar }}" alt="Avatar" style="width:28px;height:28px;border-radius:50%;object-fit:cover;flex-shrink:0;">
                                                        <div>
                                                            <div class="fw-semibold text-truncate" style="max-width:180px;">{{ $notification->title }}</div>
                                                            <div class="text-muted text-truncate" style="max-width:180px;">{{ $notification->message }}</div>
                                                            <div class="text-muted small mt-1">{{ $notification->created_at->diffForHumans() }}</div>
                                                        </div>
                                                    </div>
                                                </a>
                                            </li>
                                        @empty
                                            <li><span class="dropdown-item text-muted">No notifications</span></li>
                                        @endforelse
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a href="{{ route('notifications.index') }}" class="dropdown-item text-center">View all notifications</a></li>
                                    </ul>
                                </li>
                            @endauth
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="main-content">
            <div class="container py-4">
                @yield('content')
            </div>
        </main>

        <footer class="footer">
            <div class="container">
                <div class="text-center">
                    <p class="mb-0">&copy; {{ date('Y') }} Auto Repair Shop. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const notificationItems = document.querySelectorAll('.notification-item');
        const modalEl = document.getElementById('notificationDetailsModal');
        if (!modalEl) return;

        const modal = new bootstrap.Modal(modalEl);
        const titleEl = document.getElementById('modalNotificationTitle');
        const msgEl = document.getElementById('modalNotificationMessage');
        const timeEl = document.getElementById('modalNotificationTime');
        const senderEl = document.getElementById('modalNotificationSender');
        const senderEmailEl = document.getElementById('modalNotificationSenderEmail');
        const avatarEl = document.getElementById('modalNotificationAvatar');
        const toggleBtn = document.getElementById('toggleReadModalBtn');
        const deleteBtn = document.getElementById('deleteNotificationBtn');
        const downloadSection = document.getElementById('downloadReceiptSection');
        const downloadBtn = document.getElementById('downloadReceiptBtn');

        let currentNotificationId = null;
        let isRead = false;
        let receiptUrl = null;

        function openModalFromItem(item) {
            currentNotificationId = item.getAttribute('data-id');
            isRead = item.getAttribute('data-is-read') === '1';
            const title = item.getAttribute('data-title') || 'Notification';
            const message = item.getAttribute('data-message') || '';
            const sender = item.getAttribute('data-sender') || 'System';
            const senderEmail = item.getAttribute('data-sender-email') || '';
            const avatar = item.getAttribute('data-avatar') || avatarEl.src;
            receiptUrl = item.getAttribute('data-receipt-url');

            titleEl.textContent = title;
            msgEl.innerHTML = message;
            senderEl.textContent = sender;
            senderEmailEl.textContent = senderEmail;
            avatarEl.src = avatar;

            if (receiptUrl) {
                downloadSection.style.display = '';
                downloadBtn.href = receiptUrl;
            } else {
                downloadSection.style.display = 'none';
                downloadBtn.href = '#';
            }

            toggleBtn.textContent = isRead ? 'Mark as Unread' : 'Mark as Read';
            modal.show();
        }

        notificationItems.forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                openModalFromItem(item);
            });
        });

        // Mark read/unread
        if (toggleBtn) {
            toggleBtn.addEventListener('click', async function() {
                if (!currentNotificationId) return;
                try {
                    const resp = await fetch(`{{ route('notifications.toggleRead', ['id' => '___ID___']) }}`.replace('___ID___', currentNotificationId), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });
                    if (resp.ok) {
                        isRead = !isRead;
                        toggleBtn.textContent = isRead ? 'Mark as Unread' : 'Mark as Read';
                        // Update badge/visual on item if present
                        const item = document.querySelector(`.notification-item[data-id="${currentNotificationId}"]`);
                        if (item) item.setAttribute('data-is-read', isRead ? '1' : '0');
                    }
                } catch (_) {}
            });
        }

        // Delete
        if (deleteBtn) {
            deleteBtn.addEventListener('click', async function() {
                if (!currentNotificationId) return;
                if (!confirm('Delete this notification?')) return;
                try {
                    const resp = await fetch(`{{ route('notifications.destroy', ['id' => '___ID___']) }}`.replace('___ID___', currentNotificationId), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });
                    if (resp.ok) {
                        const item = document.querySelector(`.notification-item[data-id="${currentNotificationId}"]`);
                        if (item) item.remove();
                        modal.hide();
                    }
                } catch (_) {}
            });
        }
    });
    </script>

    <!-- Notification Details Modal -->
    <div class="modal fade" id="notificationDetailsModal" tabindex="-1" aria-labelledby="notificationDetailsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="notificationDetailsModalLabel">Notification Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center py-4">
            <div class="d-flex flex-column align-items-center mb-3">
              <img id="modalNotificationAvatar" src="{{ asset('images/default-profile.png') }}"
                   alt="Sender Avatar"
                   style="width:90px;height:90px;border-radius:50%;object-fit:cover;box-shadow:0 2px 12px rgba(44,62,80,0.10);margin-bottom:1rem;"
                   onerror="this.onerror=null;this.src='{{ asset('images/default-profile.png') }}';">
              <div class="fw-bold fs-5" id="modalNotificationSender">Sender Name</div>
              <div class="text-muted small" id="modalNotificationSenderEmail"></div>
            </div>
            <div class="mb-3">
              <h6 id="modalNotificationTitle" class="fw-semibold"></h6>
              <div id="modalNotificationMessage" class="alert alert-light border rounded-3 py-3 px-4 text-start" style="min-height:60px;"></div>
              <!-- Download Receipt Button (hidden by default) -->
              <div id="downloadReceiptSection" class="mt-3" style="display: none;">
                <a id="downloadReceiptBtn" href="#" class="btn btn-success btn-sm" target="_blank">
                  <i class="fas fa-download me-2"></i>Download Receipt
                </a>
              </div>
            </div>
            <div class="mb-2 text-muted"><strong>Time:</strong> <span id="modalNotificationTime"></span></div>
            <button id="toggleReadModalBtn" class="btn btn-outline-secondary btn-sm mt-2">Mark as Read</button>
            <button id="deleteNotificationBtn" class="btn btn-outline-danger btn-sm mt-2"><i class="fas fa-trash"></i> Delete</button>
          </div>
        </div>
      </div>
    </div>
    <style>
    #modalNotificationAvatar {
        transition: box-shadow 0.2s;
    }
    #modalNotificationAvatar:hover {
        box-shadow: 0 4px 24px rgba(44,62,80,0.18);
    }
    #modalNotificationSender {
        margin-bottom: 0.2rem;
    }
    #modalNotificationSenderEmail {
        margin-bottom: 0.5rem;
    }

    /* Comprehensive Mobile Responsive Design */
    @media (max-width: 991.98px) {
        /* Global Mobile Typography */
        body {
            font-size: 0.9rem;
            line-height: 1.5;
        }
        
        h1 { font-size: 1.75rem !important; }
        h2 { font-size: 1.5rem !important; }
        h3 { font-size: 1.25rem !important; }
        h4 { font-size: 1.1rem !important; }
        h5 { font-size: 1rem !important; }
        h6 { font-size: 0.9rem !important; }
        
        /* Adjust logo font size for mobile */
        .brand-text {
            font-size: 1.1rem !important;
            font-weight: 600;
        }
        
        .navbar-brand i {
            font-size: 1.2rem !important;
        }
        
        /* Fix toggle button positioning to right side - NO SLIDE DOWN */
        .navbar-toggler {
            padding: 0.375rem 0.75rem;
            font-size: 1.1rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            margin-left: auto;
            order: 2;
            position: relative !important;
            transform: none !important;
        }
        
        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
        }
        
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.9%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }
        
        /* Adjust navbar container for better layout */
        .navbar .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        /* Prevent navbar collapse from sliding */
        .navbar-collapse {
            position: absolute !important;
            top: 100% !important;
            left: 0 !important;
            right: 0 !important;
            background: rgba(0, 0, 0, 0.95) !important;
            backdrop-filter: blur(10px) !important;
            z-index: 1000 !important;
            max-height: 75vh !important;
            overflow-y: auto !important;
            border-radius: 0 0 15px 15px !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3) !important;
        }
        
        .navbar-nav {
            display: flex;
            flex-direction: column;
            width: 100%;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
        }
        
        /* Mobile User Profile - Styled for top position with smaller fonts */
        .mobile-user-profile .nav-link {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            width: 100%;
            text-align: left;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
            font-size: 0.9rem;
            font-weight: 500;
            color: white !important;
        }
        
        .mobile-user-profile .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            color: white !important;
        }
        
        .mobile-user-profile .nav-profile-picture {
            width: 40px !important;
            height: 40px !important;
            border-radius: 50% !important;
            object-fit: cover !important;
            display: block !important;
            flex-shrink: 0 !important;
        }
        
        .mobile-user-profile .nav-username {
            font-weight: 600;
            margin-left: 0.75rem;
            font-size: 1rem;
            color: white !important;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .mobile-user-profile .nav-profile-container {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Ensure profile images work on very small screens */
        @media (max-width: 480px) {
            .mobile-user-profile .nav-profile-picture {
                width: 35px !important;
                height: 35px !important;
            }
            
            .mobile-user-profile .nav-username {
                font-size: 0.9rem;
                margin-left: 0.5rem;
            }
        }
        
        /* Fallback for profile images that fail to load */
        .nav-profile-picture[src=""],
        .nav-profile-picture:not([src]) {
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .nav-profile-picture[src=""]:before,
        .nav-profile-picture:not([src]):before {
            content: "👤";
            font-size: 1.2em;
            color: white;
        }
        
        /* Mobile Navigation Items with smaller fonts */
        .navbar-nav .nav-item.d-lg-none .nav-link {
            padding: 0.875rem 1rem;
            border-radius: 8px;
            transition: all 0.2s ease;
            width: 100%;
            text-align: left;
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 0.375rem;
            color: white !important;
        }
        
        .navbar-nav .nav-item.d-lg-none .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            color: white !important;
        }
        
        /* Mobile Notification Bell - Styled for bottom position with smaller fonts */
        .mobile-notification-bell .nav-link {
            background: transparent;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 0.5rem;
            border: none;
            width: 100%;
            text-align: left;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
            font-size: 0.9rem;
            font-weight: 500;
            color: white !important;
        }
        
        .mobile-notification-bell .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            color: white !important;
        }
        
        /* Notification badge positioning on mobile */
        .mobile-notification-bell .position-absolute {
            /* Removed conflicting positioning - now handled by HTML structure */
        }
        
        /* Desktop notification badge positioning */
        .d-none.d-lg-block .position-absolute {
            top: 0 !important;
            right: 0 !important;
            transform: translate(-50%, -50%) !important;
            font-size: 0.75rem;
        }
        
        /* Make dropdowns full width on mobile */
        @media (max-width: 991.98px) {
            .navbar-nav .dropdown-menu {
                width: 100%;
                margin-top: 0.5rem;
                position: static !important;
                transform: none !important;
                border: 1px solid rgba(255, 255, 255, 0.2);
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                border-radius: 8px;
                padding: 0.5rem;
            }
            
            /* Style dropdown items on mobile with smaller fonts */
            .navbar-nav .dropdown-item {
                color: white !important;
                padding: 0.625rem 0.875rem;
                border-radius: 6px;
                margin: 0.25rem 0;
                font-size: 0.85rem;
                transition: all 0.2s ease;
            }
            
            .navbar-nav .dropdown-item:hover {
                background: rgba(255, 255, 255, 0.1);
                color: white !important;
                transform: translateX(2px);
            }
            
            /* Ensure proper spacing */
            .navbar-nav .nav-item {
                margin-bottom: 0;
                width: 100%;
            }
            
            /* Better icon spacing in nav links */
            .navbar-nav .nav-link i {
                margin-right: 0.625rem;
                width: 18px;
                text-align: center;
                color: white !important;
                font-size: 0.9rem;
            }
            
            /* Adjust text wrapping for longer menu items */
            .navbar-nav .nav-link span {
                flex: 1;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                color: white !important;
            }
        }
        
        /* Fix dropdown header color with smaller font */
        .navbar-nav .dropdown-header {
            color: white !important;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.5rem 0.875rem;
        }
        
        /* Notification dropdown: make title color same as message color */
        .notification-bell-item .fw-semibold {
            color: #6c757d !important;
        }
        .notification-bell-item .text-muted {
            color: #6c757d !important;
        }

        /* Fix dropdown divider */
        .navbar-nav .dropdown-divider {
            border-color: rgba(255, 255, 255, 0.2);
            margin: 0.375rem 0;
        }
        
        /* Mobile Form Improvements */
        .form-control {
            font-size: 0.9rem !important;
            padding: 0.75rem 0.75rem !important;
        }
        
        .form-label {
            font-size: 0.9rem !important;
            font-weight: 500 !important;
            color: #333 !important;
        }
        
        .input-group-text {
            font-size: 0.9rem !important;
            padding: 0.75rem 0.75rem !important;
        }
        
        /* Password eye icon positioning - ensure it stays in input group */
        .input-group .btn {
            font-size: 0.9rem !important;
            padding: 0.75rem 0.75rem !important;
        }
        
        /* Card improvements for mobile */
        .card {
            border-radius: 12px !important;
            margin-bottom: 1rem !important;
        }
        
        .card-header {
            padding: 1rem !important;
            font-size: 1.1rem !important;
        }
        
        .card-body {
            padding: 1rem !important;
        }
        
        /* Button improvements for mobile */
        .btn {
            font-size: 0.9rem !important;
            padding: 0.75rem 1rem !important;
            border-radius: 8px !important;
        }
        
        .btn-lg {
            padding: 1rem 1.5rem !important;
            font-size: 1rem !important;
        }
        
        /* Alert improvements for mobile */
        .alert {
            font-size: 0.85rem !important;
            padding: 0.75rem 1rem !important;
            border-radius: 8px !important;
        }
        
        /* Table improvements for mobile */
        .table {
            font-size: 0.85rem !important;
        }
        
        .table th,
        .table td {
            padding: 0.5rem 0.75rem !important;
        }
        
        /* Modal improvements for mobile */
        .modal-header {
            padding: 1rem !important;
        }
        
        .modal-body {
            padding: 1rem !important;
        }
        
        .modal-footer {
            padding: 1rem !important;
        }
        /* Notification modal: align header/title color with message color */
        #modalNotificationTitle {
            color: #495057 !important;
        }
        #modalNotificationMessage {
            color: #495057 !important;
        }
         
        /* Badge improvements for mobile */
        .badge {
            font-size: 0.75rem !important;
            padding: 0.25rem 0.5rem !important;
        }
        
        /* Text improvements for mobile */
        .text-muted {
            color: #6c757d !important;
        }
        
        .text-primary {
            color: #0d6efd !important;
        }
        
        .text-success {
            color: #198754 !important;
        }
        
        .text-danger {
            color: #dc3545 !important;
        }
        
        .text-warning {
            color: #ffc107 !important;
        }
        
        .text-info {
            color: #0dcaf0 !important;
        }
        
        /* Container improvements for mobile */
        .container {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }
        
        /* Spacing improvements for mobile */
        .py-4 {
            padding-top: 1.5rem !important;
            padding-bottom: 1.5rem !important;
        }
        
        .mb-4 {
            margin-bottom: 1rem !important;
        }
        
        .mt-4 {
            margin-top: 1rem !important;
        }
        
        /* Grid improvements for mobile */
        .col-md-6 {
            width: 100% !important;
        }
        
        .col-lg-4 {
            width: 100% !important;
        }
        
        .col-lg-6 {
            width: 100% !important;
        }
        
        /* Footer improvements for mobile */
        .footer {
            padding: 1rem 0 !important;
            font-size: 0.85rem !important;
        }
        
        /* Main content improvements for mobile */
        .main-content {
            min-height: calc(100vh - 200px) !important;
        }
    }
    
    /* Extra small devices (phones, 576px and down) */
    @media (max-width: 575.98px) {
        .container {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }
        
        .card-body {
            padding: 0.75rem !important;
        }
        
        .btn {
            padding: 0.625rem 0.875rem !important;
            font-size: 0.85rem !important;
        }
        
        .form-control {
            font-size: 0.85rem !important;
            padding: 0.625rem 0.75rem !important;
        }
        
        .input-group-text {
            font-size: 0.85rem !important;
            padding: 0.625rem 0.75rem !important;
        }
        
        /* Welcome page specific mobile improvements */
        .welcome-section h1 {
            font-size: 1.5rem !important;
        }
        
        .welcome-section .lead {
            font-size: 1rem !important;
        }
        
        .business-hours-card {
            margin-bottom: 1rem !important;
        }
        
        .hours-box h4 {
            font-size: 1.1rem !important;
        }
        
        .hours-box .lead {
            font-size: 0.9rem !important;
        }
        
        /* Shop cards mobile improvements */
        .shop-section {
            margin-bottom: 1rem !important;
        }
        
        .shop-section .card {
            margin-bottom: 0.75rem !important;
        }
        
        /* Time slots mobile improvements */
        .time-slot-card {
            margin-bottom: 0.5rem !important;
        }
        
        .time-slot-card .btn {
            font-size: 0.8rem !important;
            padding: 0.5rem 0.75rem !important;
        }
        
        /* Feedback section mobile improvements */
        .feedback-section {
            margin-bottom: 1rem !important;
        }
        
        .feedback-card {
            margin-bottom: 0.75rem !important;
        }
        
        /* Service cards mobile improvements */
        .service-card {
            margin-bottom: 0.75rem !important;
        }
        
        .service-card .card-title {
            font-size: 1rem !important;
        }
        
        .service-card .card-text {
            font-size: 0.85rem !important;
        }
        
        /* How it works mobile improvements */
        .how-it-works .step {
            margin-bottom: 1rem !important;
        }
        
        .how-it-works .step h4 {
            font-size: 1.1rem !important;
        }
        
        .how-it-works .step p {
            font-size: 0.9rem !important;
        }
        
        /* Customer service mobile improvements */
        .customer-service .contact-info {
            margin-bottom: 1rem !important;
        }
        
        .customer-service .contact-info h4 {
            font-size: 1.1rem !important;
        }
        
        .customer-service .contact-info p {
            font-size: 0.9rem !important;
        }
        
        /* Customer Service Index Mobile Styles */
        .customer-service-index .d-flex.justify-content-between {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 1rem;
        }
        
        .customer-service-index .btn {
            width: 100% !important;
            font-size: 0.9rem !important;
            padding: 0.75rem 1rem !important;
        }
        
        .customer-service-index .table-responsive {
            border-radius: 12px !important;
            overflow: hidden !important;
        }
        
        .customer-service-index .table {
            font-size: 0.85rem !important;
        }
        
        .customer-service-index .table th {
            padding: 0.75rem 0.5rem !important;
            font-size: 0.8rem !important;
            font-weight: 600 !important;
        }
        
        .customer-service-index .table td {
            padding: 0.75rem 0.5rem !important;
            vertical-align: top !important;
        }
        
        .customer-service-index .fw-semibold {
            font-size: 0.9rem !important;
            font-weight: 600 !important;
        }
        
        .customer-service-index .text-muted.small {
            font-size: 0.75rem !important;
        }
        
        .customer-service-index .badge {
            font-size: 0.7rem !important;
            padding: 0.35rem 0.6rem !important;
        }
        
        .customer-service-index .btn-sm {
            font-size: 0.75rem !important;
            padding: 0.4rem 0.8rem !important;
        }
        
        /* Mobile table improvements for customer service */
        .customer-service-index .table-responsive {
            border: none !important;
        }
        
        .customer-service-index .table {
            margin-bottom: 0 !important;
        }
        
        .customer-service-index .table th:first-child,
        .customer-service-index .table td:first-child {
            padding-left: 1rem !important;
        }
        
        .customer-service-index .table th:last-child,
        .customer-service-index .table td:last-child {
            padding-right: 1rem !important;
        }
        
        /* Mobile card layout for better readability */
        .customer-service-index .table tbody tr {
            border-bottom: 1px solid #dee2e6 !important;
        }
        
        .customer-service-index .table tbody tr:last-child {
            border-bottom: none !important;
        }
        
        /* Customer Service Create Mobile Styles */
        .customer-service-create .mx-auto {
            max-width: 100% !important;
            padding: 0 0.5rem !important;
        }
        
        .customer-service-create .d-flex.align-items-center {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 0.75rem;
        }
        
        .customer-service-create .btn-link {
            font-size: 1.1rem !important;
            padding: 0.5rem !important;
        }
        
        .customer-service-create .h3 {
            font-size: 1.4rem !important;
            margin-bottom: 0 !important;
        }
        
        .customer-service-create .form-label {
            font-size: 0.95rem !important;
            font-weight: 600 !important;
            margin-bottom: 0.5rem !important;
        }
        
        .customer-service-create .form-select,
        .customer-service-create .form-control {
            font-size: 0.9rem !important;
            padding: 0.75rem 0.9rem !important;
            border-radius: 8px !important;
        }
        
        .customer-service-create .form-text {
            font-size: 0.8rem !important;
        }
        
        .customer-service-create .d-flex.justify-content-end {
            flex-direction: column !important;
            gap: 0.75rem !important;
        }
        
        .customer-service-create .btn {
            width: 100% !important;
            font-size: 0.9rem !important;
            padding: 0.75rem 1rem !important;
        }
        
        .customer-service-create .alert {
            font-size: 0.85rem !important;
            padding: 1rem !important;
        }
        
        .customer-service-create .alert ul {
            padding-left: 1.2rem !important;
        }
        
        .customer-service-create .alert li {
            margin-bottom: 0.5rem !important;
        }
        
        /* Additional mobile improvements for customer service forms */
        .customer-service-create .card {
            border-radius: 12px !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
        }
        
        .customer-service-create .card-body {
            padding: 1.25rem !important;
        }
        
        .customer-service-create .mb-3 {
            margin-bottom: 1.25rem !important;
        }
        
        .customer-service-create textarea {
            resize: vertical !important;
            min-height: 120px !important;
        }
        
        .customer-service-create .form-select option {
            font-size: 0.9rem !important;
        }
        
        /* Customer Service Show Mobile Styles */
        .customer-service-show .mx-auto {
            max-width: 100% !important;
            padding: 0 0.5rem !important;
        }
        
        .customer-service-show .d-flex.align-items-center {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 0.75rem;
        }
        
        .customer-service-show .btn-link {
            font-size: 1.1rem !important;
            padding: 0.5rem !important;
        }
        
        .customer-service-show .h3 {
            font-size: 1.4rem !important;
            margin-bottom: 0 !important;
        }
        
        .customer-service-show .h5 {
            font-size: 1.1rem !important;
        }
        
        .customer-service-show .h6 {
            font-size: 1rem !important;
        }
        
        .customer-service-show .d-flex.justify-content-between {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 1rem;
        }
        
        .customer-service-show .d-flex.gap-2 {
            flex-wrap: wrap !important;
            gap: 0.5rem !important;
        }
        
        .customer-service-show .badge {
            font-size: 0.7rem !important;
            padding: 0.35rem 0.6rem !important;
        }
        
        .customer-service-show .text-muted.small {
            font-size: 0.75rem !important;
        }
        
        .customer-service-show .alert {
            font-size: 0.85rem !important;
            padding: 1rem !important;
        }
        
        .customer-service-show .d-flex.align-items-center {
            flex-direction: row !important;
            align-items: center !important;
        }
        
        .customer-service-show .me-2 {
            margin-right: 0.75rem !important;
        }
        
        .customer-service-show .fw-semibold {
            font-size: 0.9rem !important;
        }
        
        /* Additional mobile improvements for customer service show page */
        .customer-service-show .card {
            border-radius: 12px !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
            margin-bottom: 1rem !important;
        }
        
        .customer-service-show .card-body {
            padding: 1.25rem !important;
        }
        
        .customer-service-show .border-top {
            border-top: 1px solid #dee2e6 !important;
            padding-top: 1rem !important;
        }
        
        .customer-service-show .mb-3 {
            margin-bottom: 1rem !important;
        }
        
        .customer-service-show .alert-light {
            background-color: #f8f9fa !important;
            border: 1px solid #e9ecef !important;
            border-radius: 8px !important;
            padding: 1rem !important;
        }
        
        .customer-service-show .timeline-item {
            margin-bottom: 1rem !important;
        }
        
        .customer-service-show .timeline-item:last-child {
            margin-bottom: 0 !important;
        }
        
        /* Modal mobile improvements */
        .modal-dialog {
            margin: 0.5rem !important;
        }
        
        .modal-content {
            border-radius: 12px !important;
        }
        
        /* Map modal mobile improvements */
        #mapModal .modal-body {
            padding: 0.5rem !important;
        }
        
        #mapModal iframe {
            height: 300px !important;
        }
        
        /* Button groups mobile improvements */
        .btn-group .btn {
            font-size: 0.8rem !important;
            padding: 0.5rem 0.75rem !important;
        }
        
        /* Form groups mobile improvements */
        .form-group {
            margin-bottom: 1rem !important;
        }
        
        .form-text {
            font-size: 0.8rem !important;
        }
        
        /* Input groups mobile improvements */
        .input-group {
            margin-bottom: 0.75rem !important;
        }
        
        /* Password eye icon mobile positioning */
        .input-group .btn-outline-secondary {
            border-left: 1px solid #ced4da !important;
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
            position: relative !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        
        /* Ensure input group maintains proper layout */
        .input-group {
            display: flex !important;
            flex-wrap: nowrap !important;
            align-items: stretch !important;
        }
        
        .input-group .form-control {
            position: relative !important;
            flex: 1 1 auto !important;
            width: 1% !important;
            min-width: 0 !important;
        }
        
        .input-group .input-group-text {
            display: flex !important;
            align-items: center !important;
            padding: 0.75rem 0.75rem !important;
            font-size: 0.9rem !important;
            font-weight: 400 !important;
            line-height: 1.5 !important;
            color: #495057 !important;
            text-align: center !important;
            white-space: nowrap !important;
            background-color: #f8f9fa !important;
            border: 1px solid #ced4da !important;
            border-radius: 0.375rem 0 0 0.375rem !important;
        }
        
        .input-group .btn {
            position: relative !important;
            z-index: 2 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0.75rem 0.75rem !important;
            font-size: 0.9rem !important;
            font-weight: 400 !important;
            line-height: 1.5 !important;
            color: #6c757d !important;
            text-align: center !important;
            text-decoration: none !important;
            vertical-align: middle !important;
            cursor: pointer !important;
            user-select: none !important;
            background-color: transparent !important;
            border: 1px solid #ced4da !important;
            border-radius: 0 0.375rem 0.375rem 0 !important;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
        }
        
        /* Ensure all text is visible on mobile */
        .text-white {
            color: white !important;
        }
        
        .text-dark {
            color: #212529 !important;
        }
        
        .text-muted {
            color: #6c757d !important;
        }
        
        .text-primary {
            color: #0d6efd !important;
        }
        
        .text-success {
            color: #198754 !important;
        }
        
        .text-danger {
            color: #dc3545 !important;
        }
        
        .text-warning {
            color: #ffc107 !important;
        }
        
        .text-info {
            color: #0dcaf0 !important;
        }
        
        /* Force input group layout to prevent eye icon from moving below */
        .input-group > * {
            flex-shrink: 0 !important;
        }
        
        .input-group > .form-control {
            flex: 1 1 auto !important;
            min-width: 0 !important;
        }
        
        /* Ensure eye icon button stays inline */
        .input-group .btn-outline-secondary {
            flex-shrink: 0 !important;
            width: auto !important;
            min-width: 44px !important;
        }
        
        /* Override any conflicting styles */
        .input-group .btn {
            float: none !important;
            display: inline-flex !important;
            position: relative !important;
            top: auto !important;
            left: auto !important;
            right: auto !important;
            bottom: auto !important;
        }
        
        /* Specific fix for password eye icon positioning */
        .input-group .btn-outline-secondary[type="button"] {
            position: static !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin: 0 !important;
            padding: 0.75rem 0.75rem !important;
            border-left: 1px solid #ced4da !important;
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
            border-top-right-radius: 0.375rem !important;
            border-bottom-right-radius: 0.375rem !important;
        }
        
        /* Ensure input group doesn't wrap */
        .input-group {
            flex-wrap: nowrap !important;
            overflow: hidden !important;
        }
        
        /* Force inline layout for input group children */
        .input-group > .input-group-text,
        .input-group > .form-control,
        .input-group > .btn {
            display: inline-flex !important;
            vertical-align: middle !important;
            float: none !important;
        }
    }
    
    /* Small devices (landscape phones, 576px and up) */
    @media (min-width: 576px) and (max-width: 767.98px) {
        /* Add any additional styles you want for small devices */
    }
    
    /* Medium devices (tablets, 768px and up) */
    @media (min-width: 768px) and (max-width: 991.98px) {
        /* Add any additional styles you want for medium devices */
    }
    
    /* Large devices (desktops, 992px and up) */
    @media (min-width: 992px) {
        /* Add any additional styles you want for large devices */
    }
    
    /* Extra large devices (large desktops, 1200px and up) */
    @media (min-width: 1200px) {
        /* Add any additional styles you want for extra large devices */
    }
    
    /* Specific styles for large screens */
    @media (min-width: 1200px) and (max-width: 1400px) {
        /* Add any additional styles you want for screens between 1200px and 1400px */
    }
    
    /* Specific styles for extra large screens */
    @media (min-width: 1400px) {
        /* Add any additional styles you want for screens wider than 1400px */
    }
    
    /* Fix for password eye icon positioning */
    .input-group .btn-outline-secondary[type="button"] {
        position: static !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 0 !important;
        padding: 0.75rem 0.75rem !important;
        border-left: 1px solid #ced4da !important;
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
        border-top-right-radius: 0.375rem !important;
        border-bottom-right-radius: 0.375rem !important;
        flex-shrink: 0 !important;
        width: auto !important;
        min-width: 44px !important;
    }
    
    /* Ensure input group doesn't wrap */
    .input-group {
        flex-wrap: nowrap !important;
        overflow: hidden !important;
        display: flex !important;
        align-items: stretch !important;
    }
    
    /* Force inline layout for input group children */
    .input-group > .input-group-text,
    .input-group > .form-control,
    .input-group > .btn {
        display: inline-flex !important;
        vertical-align: middle !important;
        float: none !important;
    }
    
    /* Override any conflicting styles that might move the eye icon */
    .input-group .btn {
        float: none !important;
        position: relative !important;
        top: auto !important;
        left: auto !important;
        right: auto !important;
        bottom: auto !important;
    }
    
    /* Hide validation error messages on mobile */
    .invalid-feedback {
        display: block !important;
    }
    
    /* Hide validation error messages on mobile */
    .is-invalid {
        border-color: #dc3545 !important;
        box-shadow: none !important;
    }
    
    /* Hide alert messages on mobile */
    .alert {
        display: block !important;
    }
    
    /* Hide any other error messages on mobile */
    .text-danger,
    .error-message,
    .validation-error {
        display: inline !important;
    }
    </style>
    <style>
    /* Compact top spacing: ensure main page content starts ~10px below topbar on all pages */
    .page-wrapper > .container,
    .page-wrapper main > .container,
    body > .container {
        margin-top: -10px !important;
    }
    /* Reduce excessive top padding commonly set via utility classes */
    .container.py-5, .container.py-4,
    .container.pt-5, .container.pt-4,
    .py-5:first-child, .py-4:first-child,
    .pt-5:first-child, .pt-4:first-child {
        padding-top: 10px !important;
    }
    /* Remove default top margin on the first heading in page containers */
    .main-content .container > h1:first-child,
    .main-content .container > h2:first-child,
    .main-content .container > h3:first-child {
        margin-top: 0 !important;
    }
    /* Mobile: force compact spacing under navbar */
    @media (max-width: 991.98px) {
        .page-wrapper > .container,
        .page-wrapper main > .container,
        body > .container,
        .main-content .container { margin-top: -9px !important; padding-top: 0 !important; }
    }
    </style>
    <style>
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
    </style>