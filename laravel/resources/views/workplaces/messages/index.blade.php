@extends('layouts.workplace')

@section('content')
<div class="container-fluid">
    @include('workplaces.partials.site-header', ['activeTab' => 'messages'])

    <div class="row g-4">
        {{-- ==================== LISTE DES CONVERSATIONS ==================== --}}
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center bg-white">
                    <h5 class="mb-0"><i class="bi bi-chat-left-text me-2 text-primary"></i>Messages</h5>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newConversationModal">
                        <i class="bi bi-plus-lg me-1"></i>Nouveau
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 68vh; overflow-y: auto;">

                        {{-- Groupes --}}
                        <div class="list-group-item bg-light fw-bold text-uppercase small py-2">
                            <i class="bi bi-people-fill me-1 text-primary"></i>Groupes
                        </div>
                        @forelse($groups as $conv)
                            @include('workplaces.messages.partials.conversation-item', ['conv' => $conv])
                        @empty
                            <div class="list-group-item text-muted small py-2 ps-4">Aucun groupe</div>
                        @endforelse

                        {{-- Canaux de diffusion --}}
                        <div class="list-group-item bg-light fw-bold text-uppercase small py-2 mt-1">
                            <i class="bi bi-megaphone-fill me-1 text-warning"></i>Canaux de diffusion
                        </div>
                        @forelse($channels as $conv)
                            @include('workplaces.messages.partials.conversation-item', ['conv' => $conv])
                        @empty
                            <div class="list-group-item text-muted small py-2 ps-4">Aucun canal</div>
                        @endforelse

                        {{-- Messages privés --}}
                        <div class="list-group-item bg-light fw-bold text-uppercase small py-2 mt-1">
                            <i class="bi bi-envelope-fill me-1 text-success"></i>Messages privés
                        </div>
                        @forelse($privates as $conv)
                            @include('workplaces.messages.partials.conversation-item', ['conv' => $conv])
                        @empty
                            <div class="list-group-item text-muted small py-2 ps-4">Aucun message privé</div>
                        @endforelse
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
                                {{ $activeConversation->participants->count() }} membre(s)
                                @if($activeConversation->description)
                                    — {{ $activeConversation->description }}
                                @endif
                            </small>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('workplaces.messages.index', $workplace) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-left me-1"></i>Retour
                            </a>
                            @if($activeConversation->created_by === auth()->id())
                                <form method="POST" action="{{ route('workplaces.messages.destroy', [$workplace, $activeConversation]) }}"
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

                        <form method="POST" action="{{ route('workplaces.messages.send', [$workplace, $activeConversation]) }}" class="mt-3">
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
                        <h5>Sélectionnez une conversation</h5>
                        <p class="text-muted">Choisissez un groupe, un canal de diffusion ou un message privé dans la liste, ou créez une nouvelle conversation.</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newConversationModal">
                            <i class="bi bi-plus-lg me-1"></i>Nouvelle conversation
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ==================== MODALE NOUVELLE CONVERSATION ==================== --}}
    <div class="modal fade" id="newConversationModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('workplaces.messages.store', $workplace) }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nouvelle conversation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" id="convTypeSelect">
                            <option value="group">Groupe</option>
                            <option value="channel">Canal de diffusion</option>
                            <option value="private">Message privé</option>
                        </select>
                    </div>
                    <div class="mb-3" id="convNameField">
                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" id="convNameInput" placeholder="Ex. Projet Atlas, Annonces…">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Participants</label>
                        <select name="participant_ids[]" class="form-select" multiple size="8" required>
                            @foreach($members as $member)
                                @if($member->id !== auth()->id())
                                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <small class="text-muted">Maintenez Ctrl pour sélectionner plusieurs membres. Pour un message privé, un seul destinataire.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const typeSelect = document.getElementById('convTypeSelect');
        const nameField = document.getElementById('convNameField');
        const nameInput = document.getElementById('convNameInput');

        typeSelect.addEventListener('change', function () {
            const isPrivate = this.value === 'private';
            nameField.style.display = isPrivate ? 'none' : '';
            nameInput.required = !isPrivate;
        });

        // Scroll en bas du fil
        const thread = document.getElementById('messageThread');
        if (thread) thread.scrollTop = thread.scrollHeight;
    })();
</script>
@endpush
