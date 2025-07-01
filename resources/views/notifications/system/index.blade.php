@extends('layouts.app')

@section('title', 'Notifications système')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Notifications système</h1>
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
            <span id="unreadCount">{{ $notifications->where('read_at', null)->count() }}</span>
            notification(s) système non lue(s)
        </span>
    </div>

    <!-- Liste des notifications système -->
    <div id="notificationsList" class="space-y-3">
        @forelse($notifications as $notification)
            <div class="notification-item bg-white border rounded-lg shadow-sm p-4 {{ $notification->read_at ? 'opacity-75' : 'border-l-4 border-l-blue-500' }}"
                 data-id="{{ $notification->id }}">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="notification-icon">
                                @switch($notification->type ?? 'default')
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
                                        🔔
                                @endswitch
                            </span>
                            <h3 class="font-semibold text-gray-800">{{ $notification->title ?? 'Notification système' }}</h3>
                            <span class="priority-badge px-2 py-1 text-xs rounded
                                @if(($notification->priority ?? 1) >= 4) bg-red-100 text-red-800
                                @elseif(($notification->priority ?? 1) >= 3) bg-orange-100 text-orange-800
                                @elseif(($notification->priority ?? 1) >= 2) bg-yellow-100 text-yellow-800
                                @else bg-blue-100 text-blue-800
                                @endif">
                                Priorité {{ $notification->priority ?? 1 }}
                            </span>
                        </div>

                        <p class="text-gray-600 mb-2">{{ $notification->message ?? $notification->data['message'] ?? 'Détails non disponibles' }}</p>

                        @if(isset($notification->data) && is_array($notification->data))
                            <div class="flex items-center space-x-4 text-sm text-gray-500">
                                @foreach($notification->data as $key => $value)
                                    @if(!is_array($value) && $key != 'message')
                                        <span>{{ $key }}: {{ $value }}</span>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-2 text-xs text-gray-400">
                            {{ $notification->created_at->diffForHumans() }}
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 ml-4">
                        @if(!$notification->read_at)
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
                <p>Aucune notification système à afficher</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $notifications->links() }}
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Marquer une notification comme lue
        document.querySelectorAll('.mark-read-btn').forEach(button => {
            button.addEventListener('click', function() {
                const notificationId = this.getAttribute('data-id');
                
                fetch(`/workflows/notifications/system/${notificationId}/mark-read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const notificationItem = document.querySelector(`.notification-item[data-id="${notificationId}"]`);
                        notificationItem.classList.add('opacity-75');
                        notificationItem.classList.remove('border-l-4', 'border-l-blue-500');
                        this.remove();
                        
                        // Mise à jour du compteur
                        const unreadCount = document.getElementById('unreadCount');
                        unreadCount.textContent = parseInt(unreadCount.textContent) - 1;
                    }
                });
            });
        });

        // Supprimer une notification
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const notificationId = this.getAttribute('data-id');
                
                fetch(`/workflows/notifications/system/${notificationId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const notificationItem = document.querySelector(`.notification-item[data-id="${notificationId}"]`);
                        notificationItem.remove();
                        
                        // Mise à jour du compteur si nécessaire
                        const wasUnread = notificationItem.classList.contains('border-l-blue-500');
                        if (wasUnread) {
                            const unreadCount = document.getElementById('unreadCount');
                            unreadCount.textContent = parseInt(unreadCount.textContent) - 1;
                        }
                    }
                });
            });
        });

        // Marquer toutes les notifications comme lues
        document.getElementById('markAllRead').addEventListener('click', function() {
            fetch(`/workflows/notifications/system/mark-all-read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mettre à jour l'affichage
                    document.querySelectorAll('.notification-item').forEach(item => {
                        item.classList.add('opacity-75');
                        item.classList.remove('border-l-4', 'border-l-blue-500');
                    });
                    document.querySelectorAll('.mark-read-btn').forEach(btn => btn.remove());
                    document.getElementById('unreadCount').textContent = '0';
                }
            });
        });

        // Actualiser les notifications
        document.getElementById('refreshNotifications').addEventListener('click', function() {
            location.reload();
        });
    });
</script>
@endpush

@endsection
