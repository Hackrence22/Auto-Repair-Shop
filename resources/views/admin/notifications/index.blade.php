@extends('layouts.admin')

@section('title', 'Notifications')

@push('styles')
<style>
/* Notification list item styling */
.notification-list-item {
    transition: background-color 0.15s ease;
}
.notification-list-item:hover {
    background-color: #f8f9fa;
}
.notification-list-item .text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Mobile responsive styles */
@media (max-width: 768px) {
    .notification-list-item {
        padding: 0.75rem !important;
    }
    .notification-list-item .d-flex {
        flex-direction: column !important;
        gap: 0.5rem !important;
    }
    .notification-list-item img {
        width: 32px !important;
        height: 32px !important;
        align-self: flex-start !important;
    }
    .notification-list-item .flex-grow-1 {
        margin-right: 0 !important;
        margin-bottom: 0.5rem !important;
    }
    .notification-list-item .d-flex.align-items-center {
        justify-content: space-between !important;
        width: 100% !important;
    }
    .notification-list-item .btn {
        padding: 0.375rem 0.5rem !important;
        font-size: 0.8rem !important;
    }
    .notification-list-item .text-muted.small {
        font-size: 0.75rem !important;
    }
    .notification-list-item .fw-semibold {
        font-size: 0.9rem !important;
    }
    .notification-list-item .text-muted.small.text-truncate {
        font-size: 0.8rem !important;
        line-height: 1.3 !important;
    }
}

/* Modal styling */
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
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4" style="gap:0.75rem;">
        <h2 class="mb-0">Admin Notifications</h2>
        <form method="GET" action="{{ route('admin.notifications.index') }}" class="d-flex" role="search">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Search title or message...">
            </div>
        </form>
    </div>
    <div class="d-flex justify-content-end gap-2 mb-3">
        <form action="{{ route('admin.notifications.deleteAll') }}" method="POST" onsubmit="return confirm('Are you sure you want to delete all notifications?');">
            @csrf
            <button type="submit" class="btn btn-danger btn-sm">Delete All</button>
        </form>
        <form action="{{ route('admin.notifications.markAllRead') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success btn-sm">Mark All as Read</button>
        </form>
        <form action="{{ route('admin.notifications.markAllUnread') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-warning btn-sm">Mark All as Unread</button>
        </form>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="list-group">
                @forelse($notifications as $notification)
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
                                    $avatarPath = $userSender->profile_picture ?? null;
                                    if ($avatarPath && Storage::disk('public')->exists($avatarPath)) {
                                        $senderAvatar = asset('uploads/' . $avatarPath);
                                    }
                                }
                            } elseif ($notification->type === 'feedback' && str_contains($notification->title, 'Feedback Reply')) {
                                // Outgoing: Admin sent feedback reply to user
                                $senderName = 'You';
                                $senderEmail = auth('admin')->user()->email ?? null;
                                $avatarPath = auth('admin')->user()->profile_picture ?? null;
                                if ($avatarPath && Storage::disk('public')->exists($avatarPath)) {
                                    $senderAvatar = asset('uploads/' . $avatarPath);
                                } else {
                                    $senderAvatar = asset('images/default-profile.png');
                                }
                            } elseif (in_array($notification->type, ['status', 'booking', 'payment'])) {
                                // Outgoing: Admin sent status update to user
                                $senderName = 'You';
                                $senderEmail = auth('admin')->user()->email ?? null;
                                $avatarPath = auth('admin')->user()->profile_picture ?? null;
                                if ($avatarPath && Storage::disk('public')->exists($avatarPath)) {
                                    $senderAvatar = asset('uploads/' . $avatarPath);
                                } else {
                                    $senderAvatar = asset('images/default-profile.png');
                                }
                            } else {
                                // Default: User sent something to admin
                            $userSender = \App\Models\User::find($notification->user_id);
                                if ($userSender) {
                                    $senderName = $userSender->name ?? ($userSender->email ?? 'User');
                                    $senderEmail = $userSender->email ?? null;
                                    $avatarPath = $userSender->profile_picture ?? null;
                                    if ($avatarPath && Storage::disk('public')->exists($avatarPath)) {
                                        $senderAvatar = asset('uploads/' . $avatarPath);
                                    }
                                }
                            }
                        } else {
                            // System notification
                            $senderName = 'System';
                            $senderEmail = null;
                            $senderAvatar = asset('images/default-profile.png');
                        }
                    @endphp
                    <a href="#" class="list-group-item list-group-item-action notification-list-item {{ $notification->is_read ? '' : 'fw-bold' }}"
                       data-title="{{ $notification->title }}"
                       data-message="{{ $notification->message }}"
                       data-sender="{{ $senderName }}"
                       data-sender-email="{{ $senderEmail }}"
                       data-avatar="{{ $senderAvatar }}"
                       data-time="{{ $notification->created_at->format('Y-m-d H:i:s') }}"
                       data-id="{{ $notification->id }}"
                       data-is-read="{{ $notification->is_read ? '1' : '0' }}">
                        <div class="d-flex align-items-start" style="gap: 0.75rem;">
                            <img src="{{ $senderAvatar }}" 
                                 alt="Avatar" 
                                 style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                            <div class="flex-grow-1 me-3" style="min-width: 0;">
                                <div class="fw-semibold text-truncate" style="max-width: 100%;" title="{{ $notification->title }}">{{ $notification->title }}</div>
                                <div class="text-muted small text-truncate" style="max-width: 100%; line-height: 1.4;" title="{{ $notification->message }}">{{ $notification->message }}</div>
                                <div class="text-muted small mt-1">{{ $notification->created_at->diffForHumans() }}</div>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                <button class="btn btn-outline-secondary btn-sm toggle-read-btn" data-id="{{ $notification->id }}" data-is-read="{{ $notification->is_read ? '1' : '0' }}">
                                    {{ $notification->is_read ? 'Mark as Unread' : 'Mark as Read' }}
                                </button>
                                <button class="btn btn-outline-danger btn-sm delete-notification-btn" data-id="{{ $notification->id }}" title="Delete"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="text-muted text-center py-4">No notifications found.</div>
                @endforelse
            </div>
            <div class="mt-3 d-flex justify-content-center">
                {{ $notifications->withQueryString()->onEachSide(1)->links('vendor.pagination.shops') }}
            </div>
            <div class="text-center text-muted small mt-2">
                Showing {{ $notifications->firstItem() }} to {{ $notifications->lastItem() }} of {{ $notifications->total() }} results
            </div>
        </div>
    </div>
</div>
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
               style="width:90px;height:90px;border-radius:50%;object-fit:cover;box-shadow:0 2px 12px rgba(44,62,80,0.10);margin-bottom:1rem;">
          <div class="fw-bold fs-5" id="modalNotificationSender">Sender Name</div>
          <div class="text-muted small" id="modalNotificationSenderEmail"></div>
        </div>
        <div class="mb-3">
          <h6 id="modalNotificationTitleText" class="fw-semibold"></h6>
          <div id="modalNotificationMessage" class="alert alert-light border rounded-3 py-3 px-4 text-start" style="min-height:60px;"></div>
        </div>
        <div class="mb-2 text-muted"><strong>Time:</strong> <span id="modalNotificationTime"></span></div>
        <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="toggleReadModalBtn">Mark as Read</button>
        <button id="deleteNotificationBtn" class="btn btn-outline-danger btn-sm mt-2 ms-2"><i class="fas fa-trash"></i> Delete</button>
      </div>
    </div>
  </div>
</div>
<!-- Toast container -->
<div aria-live="polite" aria-atomic="true" class="position-fixed top-0 end-0 p-3" style="z-index: 1080; min-width: 300px;">
  <div id="notifToast" class="toast align-items-center text-bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="notifToastBody">
        <!-- Toast message here -->
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>
@push('scripts')
<script>
function showNotifToast(message, isSuccess = true) {
    var toastEl = document.getElementById('notifToast');
    var toastBody = document.getElementById('notifToastBody');
    toastBody.textContent = message;
    toastEl.classList.remove('text-bg-primary', 'text-bg-danger', 'text-bg-success');
    toastEl.classList.add(isSuccess ? 'text-bg-success' : 'text-bg-danger');
    var toast = new bootstrap.Toast(toastEl);
    toast.show();
}
document.addEventListener('DOMContentLoaded', function() {
    let currentNotificationId = null;
    let currentIsRead = null;
    document.querySelectorAll('.notification-list-item').forEach(function(item) {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('modalNotificationTitleText').textContent = this.getAttribute('data-title');
            document.getElementById('modalNotificationMessage').textContent = this.getAttribute('data-message');
            document.getElementById('modalNotificationSender').textContent = this.getAttribute('data-sender');
            document.getElementById('modalNotificationSenderEmail').textContent = this.getAttribute('data-sender-email');
            document.getElementById('modalNotificationAvatar').src = this.getAttribute('data-avatar');
            document.getElementById('modalNotificationTime').textContent = this.getAttribute('data-time');
            currentNotificationId = this.getAttribute('data-id');
            currentIsRead = this.getAttribute('data-is-read') === '1';
            // Set modal toggle button text
            document.getElementById('toggleReadModalBtn').textContent = currentIsRead ? 'Mark as Unread' : 'Mark as Read';
            var modal = new bootstrap.Modal(document.getElementById('notificationDetailsModal'));
            modal.show();
        });
    });
    // Toggle read/unread in modal
    const toggleReadModalBtn = document.getElementById('toggleReadModalBtn');
    if (toggleReadModalBtn) {
        toggleReadModalBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (!currentNotificationId) return;
            fetch(`/admin/notifications/toggle-read/${currentNotificationId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentIsRead = !currentIsRead;
                    document.getElementById('toggleReadModalBtn').textContent = currentIsRead ? 'Mark as Unread' : 'Mark as Read';
                    // Update list item
                    const notifItem = document.querySelector('.notification-list-item[data-id="' + currentNotificationId + '"]');
                    if (notifItem) {
                        notifItem.setAttribute('data-is-read', currentIsRead ? '1' : '0');
                        notifItem.classList.toggle('fw-bold', !currentIsRead);
                        const toggleBtn = notifItem.querySelector('.toggle-read-btn');
                        if (toggleBtn) {
                            toggleBtn.setAttribute('data-is-read', currentIsRead ? '1' : '0');
                            toggleBtn.textContent = currentIsRead ? 'Mark as Unread' : 'Mark as Read';
                        }
                    }
                    showNotifToast(currentIsRead ? 'Marked as read.' : 'Marked as unread.', true);
                } else {
                    showNotifToast('Failed to update notification.', false);
                }
            })
            .catch(() => showNotifToast('Failed to update notification.', false));
        });
    }
    // Delete notification logic for modal
    const deleteBtn = document.getElementById('deleteNotificationBtn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (!currentNotificationId) return;
            if (!confirm('Delete this notification?')) return;
            fetch(`/admin/notifications/delete/${currentNotificationId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    var modal = bootstrap.Modal.getInstance(document.getElementById('notificationDetailsModal'));
                    if (modal) modal.hide();
                    // Remove from list
                    const notifItem = document.querySelector('.notification-list-item[data-id="' + currentNotificationId + '"]');
                    if (notifItem) notifItem.remove();
                    showNotifToast('Notification deleted.', true);
                } else {
                    showNotifToast('Failed to delete notification.', false);
                }
            })
            .catch(() => showNotifToast('Failed to delete notification.', false));
        });
    }
    document.querySelectorAll('.delete-notification-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            const id = this.getAttribute('data-id');
            if (!id) return;
            if (!confirm('Delete this notification?')) return;
            fetch(`/admin/notifications/delete/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.closest('.notification-list-item').remove();
                    showNotifToast('Notification deleted.', true);
                } else {
                    showNotifToast('Failed to delete notification.', false);
                }
            })
            .catch(() => showNotifToast('Failed to delete notification.', false));
        });
    });
    document.querySelectorAll('.toggle-read-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            const id = this.getAttribute('data-id');
            let isRead = this.getAttribute('data-is-read') === '1';
            fetch(`/admin/notifications/toggle-read/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    isRead = !isRead;
                    this.setAttribute('data-is-read', isRead ? '1' : '0');
                    this.textContent = isRead ? 'Mark as Unread' : 'Mark as Read';
                    const notifItem = this.closest('.notification-list-item');
                    if (notifItem) {
                        notifItem.classList.toggle('fw-bold', !isRead);
                    }
                    showNotifToast(isRead ? 'Marked as read.' : 'Marked as unread.', true);
                } else {
                    showNotifToast('Failed to update notification.', false);
                }
            })
            .catch(() => showNotifToast('Failed to update notification.', false));
        });
    });
});
</script>
@endpush
@endsection 