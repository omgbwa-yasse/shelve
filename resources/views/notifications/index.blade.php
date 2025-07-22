@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Notifications</h1>
        <div class="flex space-x-2">
            <button id="markAllRead" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                Marquer tout comme lu
            </button>
            <button id="refreshNotifications" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                Actualiser
            </button>
        </div>
    </div>

    <!-- Compteur de notifications -->
    <div class="mb-4">
        <span class="inline-block bg-red-100 text-red-800 text-sm px-3 py-1 rounded-full">
            <span id="unreadCount">{{ $notifications->where('is_read', false)->count() }}</span>
            notification(s) non lue(s)
        </span>
    </div>

    <!-- Liste des notifications -->
    <div id="notificationsList" class="space-y-3">
        @forelse($notifications as $notification)
            <div class="notification-item bg-white border rounded-lg shadow-sm p-4 {{ $notification->is_read ? 'opacity-75' : 'border-l-4 border-l-blue-500' }}"
                 data-id="{{ $notification->id }}">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="notification-icon">
                                @switch($notification->type->icon())
                                    @case('mail')
                                        📧
                                        @break
                                    @case('clock')
                                        ⏰
                                        @break
                                    @case('exclamation-triangle')
                                        ⚠️
                                        @break
                                    @case('check')
                                        ✅
                                        @break
                                    @case('x')
                                        ❌
                                        @break
                                    @default
                                        📄
                                @endswitch
                            </span>
                            <h3 class="font-semibold text-gray-800">{{ $notification->title }}</h3>
                            <span class="priority-badge px-2 py-1 text-xs rounded
                                @if($notification->priority >= 4) bg-red-100 text-red-800
                                @elseif($notification->priority >= 3) bg-orange-100 text-orange-800
                                @elseif($notification->priority >= 2) bg-yellow-100 text-yellow-800
                                @else bg-blue-100 text-blue-800
                                @endif">
                                Priorité {{ $notification->priority }}
                            </span>
                        </div>

                        <p class="text-gray-600 mb-2">{{ $notification->message }}</p>

                        @if($notification->mail)
                            <div class="flex items-center space-x-4 text-sm text-gray-500">
                                <span>📄 Courrier: {{ $notification->mail->code }}</span>
                                <span>📝 {{ $notification->mail->name }}</span>
                                @if($notification->data && isset($notification->data['deadline']))
                                    <span>⏰ Échéance: {{ \Carbon\Carbon::parse($notification->data['deadline'])->format('d/m/Y H:i') }}</span>
                                @endif
                            </div>
                        @endif

                        <div class="mt-2 text-xs text-gray-400">
                            {{ $notification->created_at->diffForHumans() }}
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 ml-4">
                        @if(!$notification->is_read)
                            <button class="mark-read-btn text-blue-600 hover:text-blue-800 text-sm"
                                    data-id="{{ $notification->id }}">
                                Marquer comme lu
                            </button>
                        @endif
                        <button class="delete-btn text-red-600 hover:text-red-800 text-sm"
                                data-id="{{ $notification->id }}">
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-8 text-gray-500">
                <div class="text-4xl mb-4">🔔</div>
                <p>Aucune notification pour le moment</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($notifications instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Marquer une notification comme lue
    document.querySelectorAll('.mark-read-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const notificationId = this.dataset.id;
            markAsRead(notificationId);
        });
    });

    // Marquer toutes les notifications comme lues
    document.getElementById('markAllRead').addEventListener('click', function() {
        markAllAsRead();
    });

    // Supprimer une notification
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const notificationId = this.dataset.id;
            deleteNotification(notificationId);
        });
    });

    // Actualiser les notifications
    document.getElementById('refreshNotifications').addEventListener('click', function() {
        location.reload();
    });

    // Polling automatique toutes les 30 secondes
    setInterval(updateNotificationCount, 30000);

    function markAsRead(notificationId) {
        fetch(`/mails/notifications/${notificationId}/read`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            const notificationElement = document.querySelector(`[data-id="${notificationId}"]`);
            notificationElement.classList.add('opacity-75');
            notificationElement.classList.remove('border-l-4', 'border-l-blue-500');
            notificationElement.querySelector('.mark-read-btn').remove();
            updateNotificationCount();
        })
        .catch(error => console.error('Erreur:', error));
    }

    function markAllAsRead() {
        fetch('/mails/notifications/mark-all-read', {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            location.reload();
        })
        .catch(error => console.error('Erreur:', error));
    }

    function deleteNotification(notificationId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette notification ?')) {
            fetch(`/mails/notifications/${notificationId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                document.querySelector(`[data-id="${notificationId}"]`).remove();
                updateNotificationCount();
            })
            .catch(error => console.error('Erreur:', error));
        }
    }

    function updateNotificationCount() {
        fetch('/mails/notifications/unread-count')
        .then(response => response.json())
        .then(data => {
            document.getElementById('unreadCount').textContent = data.count;
        })
        .catch(error => console.error('Erreur:', error));
    }
});
</script>
@endpush
@endsection
