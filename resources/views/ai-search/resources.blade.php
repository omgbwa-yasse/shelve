@extends('layouts.app')

@section('content')
@php
    $activeTab = request('tab', 'skills');
@endphp

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-boxes me-2"></i>Ressources IA</h1>
        <a href="{{ route('ai-search.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Retour à l'assistant
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Onglets --}}
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'skills' ? 'active' : '' }}" href="{{ route('ai-search.resources', ['tab' => 'skills']) }}">
                <i class="bi bi-stars me-1"></i>Skills ({{ count($skills) }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'prompts' ? 'active' : '' }}" href="{{ route('ai-search.resources', ['tab' => 'prompts']) }}">
                <i class="bi bi-chat-square-text me-1"></i>Prompts ({{ $promptsCount }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'templates' ? 'active' : '' }}" href="{{ route('ai-search.resources', ['tab' => 'templates']) }}">
                <i class="bi bi-file-earmark-richtext me-1"></i>Templates ({{ $templates->count() }})
            </a>
        </li>
    </ul>

    {{-- ==================== SKILLS ==================== --}}
    @if($activeTab === 'skills')
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white fw-bold">
                        <i class="bi bi-box-arrow-down me-1"></i>Installer un skill (ZIP)
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">
                            Un skill est un dossier contenant un fichier <code>SKILL.md</code> (et éventuellement des ressources).
                            Chargez l'archive ZIP du skill : elle sera décompressée et installée dans le répertoire personnalisé.
                        </p>
                        <form method="POST" action="{{ route('ai-search.skills.install') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <input type="file" name="skill_zip" class="form-control" accept=".zip,application/zip" required>
                            </div>
                            <button class="btn btn-primary w-100">
                                <i class="bi bi-download me-1"></i>Installer le skill
                            </button>
                        </form>
                        <div class="alert alert-info small mt-3 mb-0">
                            <i class="bi bi-info-circle me-1"></i>Répertoires :<br>
                            Système : <code>storage/app/ai/skills/system</code><br>
                            Personnalisé : <code>storage/app/ai/skills/custom</code>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                @php $system = array_values(array_filter($skills, fn($s) => $s['record']->location === 'system')); @endphp
                @php $custom = array_values(array_filter($skills, fn($s) => $s['record']->location === 'custom')); @endphp

                <h5 class="text-muted"><i class="bi bi-gear me-1"></i>Skills système</h5>
                @forelse($system as $skill)
                    @include('ai-search.partials.skill-card', ['skill' => $skill])
                @empty
                    <div class="alert alert-light border">Aucun skill système installé.</div>
                @endforelse

                <h5 class="text-muted mt-4"><i class="bi bi-person-gear me-1"></i>Skills personnalisés</h5>
                @forelse($custom as $skill)
                    @include('ai-search.partials.skill-card', ['skill' => $skill])
                @empty
                    <div class="alert alert-light border">Aucun skill personnalisé installé.</div>
                @endforelse
            </div>
        </div>
    @endif

    {{-- ==================== PROMPTS ==================== --}}
    @if($activeTab === 'prompts')
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('settings.prompts.index') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>Gérer / Créer un prompt
            </a>
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                @forelse($prompts as $prompt)
                    <div class="d-flex justify-content-between align-items-start border-bottom py-2">
                        <div class="me-3">
                            <strong>
                                @if($prompt->is_system)
                                    <span class="badge bg-secondary me-1">Système</span>
                                @else
                                    <span class="badge bg-primary me-1">Personnalisé</span>
                                @endif
                                {{ $prompt->title }}
                            </strong>
                            <div class="text-muted small mt-1">
                                {{ \Illuminate\Support\Str::limit($prompt->content, 220) }}
                            </div>
                            @if($prompt->user)
                                <small class="text-muted">par {{ $prompt->user->name }}</small>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Aucun prompt.</p>
                @endforelse
            </div>
        </div>
    @endif

    {{-- ==================== TEMPLATES ==================== --}}
    @if($activeTab === 'templates')
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white fw-bold">
                        <i class="bi bi-upload me-1"></i>Ajouter un template
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">
                            Importez un fichier modèle (Word, Excel, PDF, texte…) que l'IA pourra lire pour générer d'autres documents.
                        </p>
                        <form method="POST" action="{{ route('ai-search.templates.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">Fichier <span class="text-danger">*</span></label>
                                <input type="file" name="template_file" class="form-control" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Nom</label>
                                <input type="text" name="name" class="form-control" placeholder="Ex. Bordereau officiel">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Catégorie</label>
                                <input type="text" name="category" class="form-control" placeholder="Ex. Bordereaux, Courriers">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2"></textarea>
                            </div>
                            <button class="btn btn-primary w-100">
                                <i class="bi bi-upload me-1"></i>Ajouter
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Catégorie</th>
                                        <th>Taille</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($templates as $template)
                                        <tr>
                                            <td>
                                                <i class="bi bi-file-earmark me-1 text-primary"></i>{{ $template->name }}
                                                <div class="small text-muted">{{ $template->file_name }}</div>
                                            </td>
                                            <td>{{ $template->category ?? '—' }}</td>
                                            <td>{{ number_format($template->size / 1024, 1) }} Ko</td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                                        onclick="previewTemplate({{ $template->id }}, '{{ $template->name }}')" title="Voir le contenu (que l'IA lit)">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <a href="{{ route('ai-search.templates.download', $template) }}" class="btn btn-sm btn-outline-primary" title="Télécharger">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                <form method="POST" action="{{ route('ai-search.templates.destroy', $template) }}" class="d-inline"
                                                      onsubmit="return confirm('Supprimer ce template ?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">Aucun template.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Modale contenu template --}}
<div class="modal fade" id="templatePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="templatePreviewTitle">Contenu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="templatePreviewBody"><div class="text-muted">Chargement…</div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function previewTemplate(id, name) {
        const modalEl = document.getElementById('templatePreviewModal');
        document.getElementById('templatePreviewTitle').textContent = 'Contenu — ' + name;
        document.getElementById('templatePreviewBody').innerHTML = '<div class="text-muted">Chargement…</div>';
        new bootstrap.Modal(modalEl).show();

        fetch('/ai-search/templates/' + id + '/preview')
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('templatePreviewBody').innerHTML =
                        '<pre class="border rounded p-3 bg-light" style="white-space:pre-wrap; max-height:60vh; overflow:auto;">' +
                        escapeHtml(data.content || '(contenu vide)') + '</pre>';
                } else {
                    document.getElementById('templatePreviewBody').innerHTML =
                        '<div class="alert alert-warning mb-0">' + escapeHtml(data.error || 'Lecture impossible.') + '</div>';
                }
            })
            .catch(() => {
                document.getElementById('templatePreviewBody').innerHTML =
                    '<div class="alert alert-danger mb-0">Erreur de lecture du fichier.</div>';
            });
    }

    function escapeHtml(s) {
        return (s || '').replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c];
        });
    }
</script>
@endpush
