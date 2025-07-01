@extends('layouts.app')

@section('title', 'Détail de la notification système')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <a href="{{ route('workflows.notifications.system.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="bi bi-arrow-left me-2"></i>Retour aux notifications système
        </a>
    </div>

    <div class="bg-white border rounded-lg shadow-sm p-6">
        <div class="flex items-center space-x-3 mb-4">
            <span class="notification-icon text-2xl">
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
            <h1 class="text-2xl font-bold text-gray-800">{{ $notification->title ?? 'Notification système' }}</h1>

            <span class="priority-badge px-2 py-1 text-xs rounded
                @if(($notification->priority ?? 1) >= 4) bg-red-100 text-red-800
                @elseif(($notification->priority ?? 1) >= 3) bg-orange-100 text-orange-800
                @elseif(($notification->priority ?? 1) >= 2) bg-yellow-100 text-yellow-800
                @else bg-blue-100 text-blue-800
                @endif">
                Priorité {{ $notification->priority ?? 1 }}
            </span>
        </div>

        <div class="text-sm text-gray-500 mb-4">
            Créée le {{ $notification->created_at->format('d/m/Y à H:i') }}
            @if($notification->read_at)
                <span class="ml-3 text-green-600">
                    <i class="bi bi-check-circle mr-1"></i>
                    Lue le {{ $notification->read_at->format('d/m/Y à H:i') }}
                </span>
            @else
                <span class="ml-3 text-blue-600">
                    <i class="bi bi-circle mr-1"></i>
                    Non lue
                </span>
            @endif
        </div>

        <div class="border-t border-gray-200 pt-4">
            <div class="prose max-w-none">
                <p class="text-gray-700">{{ $notification->message ?? $notification->data['message'] ?? 'Aucun détail disponible' }}</p>
            </div>
        </div>

        @if(isset($notification->data) && is_array($notification->data))
            <div class="mt-6 border-t border-gray-200 pt-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-3">Informations complémentaires</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($notification->data as $key => $value)
                        @if(!is_array($value) && $key != 'message')
                            <div class="bg-gray-50 p-3 rounded">
                                <span class="font-medium text-gray-700">{{ ucfirst($key) }}:</span>
                                <span class="text-gray-600">{{ $value }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        @if($notification->action_url)
            <div class="mt-6 border-t border-gray-200 pt-4">
                <a href="{{ $notification->action_url }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Accéder à la ressource
                </a>
            </div>
        @endif

        <div class="mt-6 border-t border-gray-200 pt-4 flex justify-end space-x-3">
            @if(!$notification->read_at)
                <button id="markAsRead" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Marquer comme lu
                </button>
            @endif
            <button id="deleteNotification" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                Supprimer
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Marquer comme lu
        const markAsReadBtn = document.getElementById('markAsRead');
        if (markAsReadBtn) {
            markAsReadBtn.addEventListener('click', function() {
                fetch(`/workflows/notifications/system/{{ $notification->id }}/mark-read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
            });
        }

        // Supprimer
        document.getElementById('deleteNotification').addEventListener('click', function() {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette notification système ?')) {
                fetch(`/workflows/notifications/system/{{ $notification->id }}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '{{ route('workflows.notifications.system.index') }}';
                    }
                });
            }
        });
    });
</script>
@endpush

@endsection
