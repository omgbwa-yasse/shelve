@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row g-4">
        {{-- ==================== LISTE DES CONVERSATIONS ==================== --}}
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center bg-white">
                    <h5 class="mb-0"><i class="bi bi-chat-left-text me-2 text-primary"></i>Chat</h5>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newPrivateModal">
                        <i class="bi bi-plus-lg me-1"></i>Nouveau message privé
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 68vh; overflow-y: auto;">

                        {{-- Messages privés entre collègues --}}
                        <div class="list-group-item bg-light fw-bold text-uppercase small py-2" id="privates">
                            <i class="bi bi-envelope-fill me-1 text-success"></i>Messages privés
                        </div>
                        @forelse($directs as $conv)
                            @include('chats.partials.conversation-item', ['conv' => $conv])
                        @empty
                            <div class="list-group-item text-muted small py-2 ps-4">Aucun message privé</div>
                        @endforelse

                        {{-- Conversations des workplaces --}}
                        @php
                            $workplaces = $workplaceConvs->groupBy(fn ($c) => $c->workplace_id);
                        @endphp
                        @foreach($workplaces as $wid => $convs)
                            <div class="list-group-item bg-light fw-bold text-uppercase small py-2 mt-1">
                                <i class="bi bi-briefcase-fill me-1 text-primary"></i>{{ $convs->first()->workplace?->name ?? 'Workplace' }}
                            </div>
                            @foreach($convs as $conv)
                                @include('chats.partials.conversation-item', ['conv' => $conv])
                            @endforeach
                        @endforeach
                        @if($workplaces->isEmpty())
                            <div class="list-group-item text-muted small py-2 ps-4">Aucune conversation de workplace</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== FIL DE CONVERSATION ==================== --}}
        <div class="col-lg-8">
            @if($activeConversation)
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center bg-white">
                        <div>
                            <h5 class="mb-0">
                                @if($activeConversation->type === 'private')
                                    @php $other = $activeConversation->participants->first(fn($p) => $p->user_id !== auth()->id()); @endphp
                                    <i class="bi bi-envelope-fill me-2 text-success"></i>{{ $other?->user?->name ?? 'Message privé' }}
                                @elseif($activeConversation->type === 'channel')
                                    <i class="bi bi-megaphone-fill me-2 text-warning"></i>{{ $activeConversation->name }}
                                @else
                                    <i class="bi bi-people-fill me-2 text-primary"></i>{{ $activeConversation->name }}
                                @endif
                            </h5>
                            <small class="text-muted">
                                @if($activeConversation->workplace)
                                    <i class="bi bi-briefcase me-1"></i>{{ $activeConversation->workplace->name }} —
                                @endif
                                {{ $activeConversation->participants->count() }} membre(s)
                            </small>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('chats.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-left me-1"></i>Retour
                            </a>
                            @if($activeConversation->created_by === auth()->id())
                                <form method="POST" action="{{ route('chats.destroy', $activeConversation) }}"
                                      onsubmit="return confirm('Supprimer cette conversation ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="messageThread" style="max-height: 52vh; overflow-y: auto; display: flex; flex-direction: column; gap: 0.5rem;">
                            @forelse($activeConversation->messages as $message)
                                @if($message->user_id === auth()->id())
                                    <div class="align-self-end" style="max-width: 75%;">
                                        <div class="p-2 px-3 rounded-3 text-white" style="background: #0d6efd;">
                                            {{ $message->content }}
                                        </div>
                                        <small class="text-muted d-block text-end mt-1">{{ $message->created_at->format('d/m H:i') }}</small>
                                    </div>
                                @else
                                    <div class="align-self-start d-flex gap-2" style="max-width: 75%;">
                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; flex-shrink: 0; font-size: 0.8rem;">
                                            {{ strtoupper(substr($message->user?->name ?? '?', 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="p-2 px-3 rounded-3" style="background: #f0f3f8;">
                                                <strong class="small">{{ $message->user?->name }}</strong><br>
                                                {{ $message->content }}
                                            </div>
                                            <small class="text-muted mt-1 d-block">{{ $message->created_at->format('d/m H:i') }}</small>
                                        </div>
                                    </div>
                                @endif
                            @empty
                                <div class="text-center text-muted py-5">Aucun message. Lancez la conversation !</div>
                            @endforelse
                        </div>

                        <form method="POST" action="{{ route('chats.send', $activeConversation) }}" class="mt-3">
                            @csrf
                            <div class="input-group">
                                <textarea name="content" class="form-control" rows="2" placeholder="Écrire un message…" required></textarea>
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-send me-1"></i>Envoyer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center text-center" style="min-height: 60vh;">
                        <div class="display-4 mb-3 text-primary"><i class="bi bi-chat-dots"></i></div>
                        <h5>Bienvenue sur le Chat</h5>
                        <p class="text-muted">Retrouvez ici toutes vos conversations de workplace et écrivez des messages privés à vos collègues.</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newPrivateModal">
                            <i class="bi bi-plus-lg me-1"></i>Nouveau message privé
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ==================== MODALE NOUVEAU MESSAGE PRIVÉ ==================== --}}
    <div class="modal fade" id="newPrivateModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('chats.store') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-envelope-plus me-2"></i>Nouveau message privé</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Collègue <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select" required>
                            @foreach($colleagues as $colleague)
                                <option value="{{ $colleague->id }}">{{ $colleague->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Ouvrir la discussion</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const thread = document.getElementById('messageThread');
        if (thread) thread.scrollTop = thread.scrollHeight;

        if (new URLSearchParams(window.location.search).get('new') === '1') {
            const modalEl = document.getElementById('newPrivateModal');
            if (modalEl && window.bootstrap) new bootstrap.Modal(modalEl).show();
        }
    })();
</script>
@endpush
