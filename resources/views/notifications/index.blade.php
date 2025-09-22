@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4" style="gap:0.75rem;">
        <h2 class="mb-0">My Notifications</h2>
        <form method="GET" action="{{ route('notifications.index') }}" class="d-flex" role="search">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Search title or message...">
            </div>
        </form>
    </div>
    <div class="d-flex justify-content-end gap-2 mb-3">
        <form action="{{ route('notifications.deleteAll') }}" method="POST" onsubmit="return confirm('Are you sure you want to delete all notifications?');">
            @csrf
            <button type="submit" class="btn btn-danger btn-sm">Delete All</button>
        </form>
        <form action="{{ route('notifications.markAllRead') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success btn-sm">Mark All as Read</button>
        </form>
        <form action="{{ route('notifications.markAllUnread') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-warning btn-sm">Mark All as Unread</button>
        </form>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="list-group">
                @forelse($notifications as $notification)
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
                                    $senderAvatar = asset('uploads/' . $avatarPath);
                                }
                            }
                        } elseif ($notification->user_id) {
                            $sender = \App\Models\User::find($notification->user_id);
                            if ($sender) {
                                $senderName = $sender->name ?? ($sender->email ?? 'User');
                                $senderEmail = $sender->email ?? null;
                                $avatarPath = $sender->profile_picture ?? null;
                                if ($avatarPath && Storage::disk('public')->exists($avatarPath)) {
                                    $senderAvatar = asset('uploads/' . $avatarPath);
                                }
                            }
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
                                <div class="text-muted small mt-1 d-none d-md-block">{{ $notification->created_at->diffForHumans() }}</div>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                <div class="text-muted small d-md-none">{{ $notification->created_at->diffForHumans() }}</div>
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
            <div class="d-flex justify-content-center mt-2 mb-1">
                {{ $notifications->withQueryString()->onEachSide(1)->links('vendor.pagination.shops') }}
            </div>
            <div class="text-center text-muted small">
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
</style>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentNotificationId = null;
    let currentNotificationIsRead = null;
    document.querySelectorAll('.notification-list-item').forEach(function(item) {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('modalNotificationTitle').textContent = this.getAttribute('data-title');
            document.getElementById('modalNotificationMessage').textContent = this.getAttribute('data-message');
            document.getElementById('modalNotificationSender').textContent = this.getAttribute('data-sender');
            document.getElementById('modalNotificationSenderEmail').textContent = this.getAttribute('data-sender-email') || '';
            document.getElementById('modalNotificationTime').textContent = this.getAttribute('data-time');
            
            // Set avatar
            const avatar = this.getAttribute('data-avatar');
            const avatarEl = document.getElementById('modalNotificationAvatar');
            if (avatar) avatarEl.src = avatar;
            
            currentNotificationId = this.getAttribute('data-id');
            currentNotificationIsRead = this.getAttribute('data-is-read') === '1';
            
            // Set button text
            const toggleBtn = document.getElementById('toggleReadModalBtn');
            if (toggleBtn) {
                toggleBtn.textContent = currentNotificationIsRead ? 'Mark as Unread' : 'Mark as Read';
            }

            // Hide download receipt section by default
            document.getElementById('downloadReceiptSection').style.display = 'none';

            // Fetch full notification data to check for receipt
            fetch(`/notifications/${currentNotificationId}`)
                .then(response => response.json())
                .then(notificationData => {
                    if (notificationData.data && notificationData.data.receipt_pdf) {
                        // Show download receipt button
                        const downloadSection = document.getElementById('downloadReceiptSection');
                        const downloadBtn = document.getElementById('downloadReceiptBtn');
                        downloadBtn.href = `{{ asset('uploads') }}/${notificationData.data.receipt_pdf}`;
                        downloadSection.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.log('Could not fetch notification data for receipt');
                });

            var modal = new bootstrap.Modal(document.getElementById('notificationDetailsModal'));
            modal.show();
        });
    });
    // Delete notification logic for modal
    const deleteBtn = document.getElementById('deleteNotificationBtn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (!currentNotificationId) return;
            if (!confirm('Delete this notification?')) return;
            fetch(`/notifications/delete/${currentNotificationId}`, {
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
                    location.reload();
                }
            });
        });
    }
    document.querySelectorAll('.delete-notification-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            const id = this.getAttribute('data-id');
            if (!id) return;
            if (!confirm('Delete this notification?')) return;
            fetch(`/notifications/delete/${id}`, {
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
                }
            });
        });
    });
    document.querySelectorAll('.toggle-read-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            const id = this.getAttribute('data-id');
            let isRead = this.getAttribute('data-is-read') === '1';
            fetch(`/notifications/toggle-read/${id}`, {
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
                }
            });
        });
    });
});
</script>
@endpush
@endsection 