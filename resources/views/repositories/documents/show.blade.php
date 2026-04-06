@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 py-2 gd-doc-view">

    {{-- ===== TOP BAR ===== --}}
    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">

        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="me-auto">
            <ol class="breadcrumb mb-0" style="font-size:.82rem;">
                <li class="breadcrumb-item"><a href="{{ route('documents.index') }}" class="text-decoration-none">Documents</a></li>
                @if($document->folder)
                <li class="breadcrumb-item"><a href="{{ route('folders.show', $document->folder) }}" class="text-decoration-none">{{ $document->folder->name }}</a></li>
                @endif
                <li class="breadcrumb-item active fw-semibold text-truncate" style="max-width:200px;">{{ $document->name }}</li>
            </ol>
        </nav>

        {{-- Action buttons --}}
        <div class="d-flex gap-2 align-items-center flex-shrink-0 flex-wrap">
            @if($document->is_current_version)
            {{-- Office edit buttons --}}
            @php $ext = strtolower(pathinfo($document->file_path ?? '', PATHINFO_EXTENSION)); @endphp
            @if(in_array($ext, ['doc','docx']))
            <a href="ms-word:ofe|u|{{ url('storage/' . $document->file_path) }}" class="btn btn-sm btn-outline-primary" title="Ouvrir dans Word">
                <i class="bi bi-file-earmark-word me-1"></i>Word
            </a>
            @elseif(in_array($ext, ['xls','xlsx']))
            <a href="ms-excel:ofe|u|{{ url('storage/' . $document->file_path) }}" class="btn btn-sm btn-outline-success" title="Ouvrir dans Excel">
                <i class="bi bi-file-earmark-excel me-1"></i>Excel
            </a>
            @elseif(in_array($ext, ['ppt','pptx']))
            <a href="ms-powerpoint:ofe|u|{{ url('storage/' . $document->file_path) }}" class="btn btn-sm btn-outline-danger" title="Ouvrir dans PowerPoint">
                <i class="bi bi-file-earmark-ppt me-1"></i>PowerPoint
            </a>
            @endif

            <a href="{{ route('documents.edit', $document) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i>Modifier
            </a>
            <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#uploadVersionModal">
                <i class="bi bi-upload me-1"></i>Nouvelle version
            </button>
            @endif

            @if($document->file_path)
            <a href="{{ asset('storage/' . $document->file_path) }}" class="btn btn-sm btn-primary" download>
                <i class="bi bi-download me-1"></i>Télécharger
            </a>
            @endif

            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2 mb-2" style="font-size:.83rem;">
        {{ session('success') }}<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- ===== MAIN LAYOUT ===== --}}
    <div class="d-flex gap-3">

        {{-- ===== PREVIEW + INFO ===== --}}
        <div class="flex-grow-1 min-w-0">

            {{-- Doc header chip --}}
            <div class="gd-doc-chip d-flex align-items-center gap-3 px-3 py-2 mb-3 rounded-3">
                @php
                    $ext = strtolower(pathinfo($document->file_path ?? '', PATHINFO_EXTENSION));
                    [$iconClass, $iconColor] = match(true) {
                        $ext === 'pdf'                          => ['bi-file-earmark-pdf', '#e53935'],
                        in_array($ext, ['doc','docx'])          => ['bi-file-earmark-word', '#1a73e8'],
                        in_array($ext, ['xls','xlsx'])          => ['bi-file-earmark-excel', '#188038'],
                        in_array($ext, ['ppt','pptx'])          => ['bi-file-earmark-ppt', '#e8710a'],
                        in_array($ext, ['jpg','jpeg','png','gif','webp']) => ['bi-file-earmark-image', '#00897b'],
                        in_array($ext, ['zip','rar','7z'])      => ['bi-file-earmark-zip', '#f9a825'],
                        default                                 => ['bi-file-earmark-text', '#5f6368'],
                    };
                @endphp
                <div style="width:44px;height:44px;background:{{ $iconColor }}18;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi {{ $iconClass }}" style="font-size:1.5rem;color:{{ $iconColor }};"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold text-truncate">{{ $document->name }}</div>
                    <div class="d-flex flex-wrap gap-2 mt-1" style="font-size:.7rem;color:#5f6368;">
                        <span class="badge border text-muted bg-white" style="font-size:.65rem;">v{{ $document->version_number }}</span>
                        @if($document->is_current_version)
                        <span class="badge" style="background:#e6f4ea;color:#188038;font-size:.65rem;">Version actuelle</span>
                        @endif
                        <span>{{ $document->type->name ?? '' }}</span>
                        @if($document->file_size_human ?? false)
                        <span><i class="bi bi-hdd me-1"></i>{{ $document->file_size_human }}</span>
                        @endif

                        {{-- Status badges --}}
                        @if($document->isCheckedOut())
                        <span class="badge" style="background:#fff3e0;color:#e65100;font-size:.65rem;"><i class="bi bi-lock-fill me-1"></i>Réservé — {{ $document->checkedOutUser->name ?? '' }}</span>
                        @endif
                        @if($document->signature_status === 'signed')
                        <span class="badge" style="background:#e6f4ea;color:#188038;font-size:.65rem;"><i class="bi bi-patch-check-fill me-1"></i>Signé</span>
                        @elseif($document->signature_status === 'pending')
                        <span class="badge" style="background:#fff3e0;color:#e65100;font-size:.65rem;"><i class="bi bi-hourglass me-1"></i>Signature en attente</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ===== PREVIEW ===== --}}
            <div class="card border-0 shadow-sm mb-3 overflow-hidden">
                @php
                    $previewable = false;
                    $previewType = null;
                    if ($document->file_path) {
                        $ext2 = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
                        if ($ext2 === 'pdf') { $previewable = true; $previewType = 'pdf'; }
                        elseif (in_array($ext2, ['jpg','jpeg','png','gif','webp'])) { $previewable = true; $previewType = 'image'; }
                        elseif (in_array($ext2, ['mp4','webm','ogg'])) { $previewable = true; $previewType = 'video'; }
                        elseif (in_array($ext2, ['txt','md','csv'])) { $previewable = true; $previewType = 'text'; }
                    }
                    $fileUrl = $document->file_path ? asset('storage/' . $document->file_path) : null;
                @endphp

                @if($previewable && $fileUrl)

                    @if($previewType === 'pdf')
                    <div class="gd-preview-toolbar d-flex align-items-center gap-2 px-3 py-2 border-bottom" style="background:#f8f9fa;">
                        <i class="bi bi-file-earmark-pdf text-danger me-1"></i>
                        <span class="small text-muted text-truncate flex-grow-1">{{ basename($document->file_path) }}</span>
                        <a href="{{ $fileUrl }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Ouvrir dans un nouvel onglet"><i class="bi bi-box-arrow-up-right"></i></a>
                        <a href="{{ $fileUrl }}" download class="btn btn-sm btn-outline-secondary" title="Télécharger"><i class="bi bi-download"></i></a>
                    </div>
                    <iframe src="{{ $fileUrl }}#toolbar=1&navpanes=0&scrollbar=1"
                            style="width:100%;height:620px;border:none;display:block;"
                            loading="lazy"
                            title="{{ $document->name }}">
                        <div class="p-4 text-center text-muted">
                            <p>Votre navigateur ne supporte pas l'aperçu PDF.</p>
                            <a href="{{ $fileUrl }}" class="btn btn-primary" target="_blank">Ouvrir le PDF</a>
                        </div>
                    </iframe>

                    @elseif($previewType === 'image')
                    <div class="gd-preview-toolbar d-flex align-items-center gap-2 px-3 py-2 border-bottom" style="background:#f8f9fa;">
                        <i class="bi bi-file-earmark-image text-info me-1"></i>
                        <span class="small text-muted text-truncate flex-grow-1">{{ basename($document->file_path) }}</span>
                        <a href="{{ $fileUrl }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrows-fullscreen"></i></a>
                        <a href="{{ $fileUrl }}" download class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a>
                    </div>
                    <div class="d-flex align-items-center justify-content-center p-3" style="background:#f5f5f5;min-height:400px;">
                        <img src="{{ $fileUrl }}" alt="{{ $document->name }}"
                             style="max-width:100%;max-height:580px;object-fit:contain;border-radius:4px;box-shadow:0 2px 8px rgba(0,0,0,.1);"
                             loading="lazy">
                    </div>

                    @elseif($previewType === 'video')
                    <div class="p-3" style="background:#000;">
                        <video controls style="width:100%;max-height:500px;display:block;">
                            <source src="{{ $fileUrl }}">
                            Votre navigateur ne supporte pas la lecture vidéo.
                        </video>
                    </div>

                    @elseif($previewType === 'text')
                    <div class="gd-preview-toolbar d-flex align-items-center gap-2 px-3 py-2 border-bottom" style="background:#f8f9fa;">
                        <i class="bi bi-file-earmark-text text-secondary me-1"></i>
                        <span class="small text-muted flex-grow-1">{{ basename($document->file_path) }}</span>
                        <a href="{{ $fileUrl }}" download class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a>
                    </div>
                    <pre class="p-3 mb-0" id="textPreviewContent" style="max-height:500px;overflow:auto;font-size:.8rem;background:#fafafa;"></pre>
                    <script>
                    fetch('{{ $fileUrl }}').then(r=>r.text()).then(t=>{
                        const el = document.getElementById('textPreviewContent');
                        if(el) el.textContent = t;
                    }).catch(()=>{});
                    </script>
                    @endif

                @elseif($document->file_path)
                    {{-- Non-previewable file --}}
                    <div class="text-center py-5" style="background:#fafafa;">
                        <div style="width:72px;height:72px;border-radius:16px;background:{{ $iconColor }}18;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                            <i class="bi {{ $iconClass }}" style="font-size:2.2rem;color:{{ $iconColor }};"></i>
                        </div>
                        <div class="fw-semibold mb-1">{{ basename($document->file_path) }}</div>
                        <div class="text-muted mb-3" style="font-size:.82rem;">La prévisualisation n'est pas disponible pour ce type de fichier</div>
                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            <a href="{{ asset('storage/' . $document->file_path) }}" class="btn btn-primary btn-sm" download>
                                <i class="bi bi-download me-1"></i>Télécharger
                            </a>
                            @if(in_array($ext, ['doc','docx']))
                            <a href="ms-word:ofe|u|{{ url('storage/' . $document->file_path) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-file-earmark-word me-1"></i>Ouvrir dans Word
                            </a>
                            @elseif(in_array($ext, ['xls','xlsx']))
                            <a href="ms-excel:ofe|u|{{ url('storage/' . $document->file_path) }}" class="btn btn-outline-success btn-sm">
                                <i class="bi bi-file-earmark-excel me-1"></i>Ouvrir dans Excel
                            </a>
                            @elseif(in_array($ext, ['ppt','pptx']))
                            <a href="ms-powerpoint:ofe|u|{{ url('storage/' . $document->file_path) }}" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-file-earmark-ppt me-1"></i>Ouvrir dans PowerPoint
                            </a>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-file-earmark-x d-block mb-2" style="font-size:2.5rem;opacity:.3;"></i>
                        Aucun fichier attaché à ce document.
                    </div>
                @endif
            </div>

            {{-- ===== INFORMATIONS ===== --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header py-2" style="background:#fafbfc;border-bottom:1px solid #eef0f3;">
                    <span class="fw-semibold" style="font-size:.85rem;">Informations générales</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0" style="font-size:.8rem;">
                        <tbody>
                            <tr><td class="text-muted ps-3 py-2 border-0" style="width:160px;">Code</td><td class="py-2 border-0"><code style="font-size:.75rem;">{{ $document->code }}</code></td></tr>
                            <tr><td class="text-muted ps-3 py-2">Type</td><td class="py-2">{{ $document->type->name ?? '—' }}</td></tr>
                            <tr><td class="text-muted ps-3 py-2">Dossier</td><td class="py-2">
                                @if($document->folder)<a href="{{ route('folders.show', $document->folder) }}" class="text-decoration-none">{{ $document->folder->name }}</a>@else —@endif
                            </td></tr>
                            <tr><td class="text-muted ps-3 py-2">Description</td><td class="py-2 text-muted">{{ $document->description ?? '—' }}</td></tr>
                            <tr><td class="text-muted ps-3 py-2">Créateur</td><td class="py-2">{{ $document->creator->name ?? '—' }}</td></tr>
                            <tr><td class="text-muted ps-3 py-2">Date document</td><td class="py-2">{{ $document->document_date?->format('d/m/Y') ?? '—' }}</td></tr>
                            <tr><td class="text-muted ps-3 py-2">Créé le</td><td class="py-2">{{ $document->created_at->format('d/m/Y H:i') }}</td></tr>
                            <tr><td class="text-muted ps-3 py-2">Modifié le</td><td class="py-2">{{ $document->updated_at->format('d/m/Y H:i') }}</td></tr>
                            <tr><td class="text-muted ps-3 py-2">Accès</td><td class="py-2"><span class="badge bg-info text-white" style="font-size:.65rem;">{{ ucfirst($document->access_level ?? '') }}</span></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- ===== RIGHT PANEL ===== --}}
        <div style="width:270px;min-width:270px;flex-shrink:0;" class="d-none d-lg-block">

            {{-- ===== VERSIONS ===== --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header py-2 d-flex align-items-center justify-content-between" style="background:#fafbfc;border-bottom:1px solid #eef0f3;">
                    <span class="fw-semibold" style="font-size:.83rem;"><i class="bi bi-clock-history me-2 text-primary"></i>Versions ({{ $versions->count() }})</span>
                    <a href="{{ route('documents.versions', $document) }}" class="btn btn-sm btn-link p-0 text-decoration-none" style="font-size:.75rem;">Tout voir</a>
                </div>
                <div class="card-body p-0">
                    @if($versions->count() > 0)
                    <div style="max-height:260px;overflow-y:auto;">
                        @foreach($versions->sortByDesc('version_number') as $ver)
                        <div class="px-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }} d-flex align-items-start gap-2 {{ $ver->is_current_version ? 'gd-ver-current' : '' }}">
                            <div class="flex-shrink-0 mt-1">
                                <div class="rounded-circle d-flex align-items-center justify-content-center {{ $ver->is_current_version ? 'bg-primary' : 'bg-light border' }}"
                                     style="width:22px;height:22px;font-size:.6rem;font-weight:700;color:{{ $ver->is_current_version ? '#fff' : '#5f6368' }};">
                                    {{ $ver->version_number }}
                                </div>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex align-items-center gap-1">
                                    <span class="fw-semibold" style="font-size:.78rem;">v{{ $ver->version_number }}</span>
                                    @if($ver->is_current_version)
                                    <span class="badge" style="font-size:.55rem;background:#e8f0fe;color:#1a73e8;">Actuelle</span>
                                    @endif
                                </div>
                                <div class="text-muted" style="font-size:.68rem;">
                                    {{ $ver->created_at->format('d/m/Y H:i') }}<br>
                                    par {{ $ver->creator->name ?? '—' }}
                                </div>
                                @if($ver->version_notes)
                                <div class="text-muted fst-italic" style="font-size:.67rem;">{{ Str::limit($ver->version_notes, 60) }}</div>
                                @endif
                            </div>
                            <div class="flex-shrink-0 d-flex gap-1">
                                @include('repositories.documents.partials.version-actions', ['version' => $ver, 'currentDocument' => $document])
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center text-muted py-3" style="font-size:.78rem;">Aucune version disponible.</div>
                    @endif
                    @if($document->is_current_version)
                    <div class="border-top p-2">
                        <button class="btn btn-outline-success btn-sm w-100" data-bs-toggle="modal" data-bs-target="#uploadVersionModal">
                            <i class="bi bi-upload me-1"></i>Téléverser une nouvelle version
                        </button>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ===== STATUTS ===== --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header py-2" style="background:#fafbfc;border-bottom:1px solid #eef0f3;">
                    <span class="fw-semibold" style="font-size:.83rem;"><i class="bi bi-shield-check me-2 text-secondary"></i>Statuts</span>
                </div>
                <div class="card-body px-3 py-2">
                    <div class="d-flex flex-column gap-2" style="font-size:.78rem;">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-muted">Document</span>
                            @if($document->status === 'active')
                                <span class="badge" style="background:#e6f4ea;color:#188038;">Actif</span>
                            @elseif($document->status === 'draft')
                                <span class="badge bg-secondary">Brouillon</span>
                            @elseif($document->status === 'archived')
                                <span class="badge" style="background:#fff3e0;color:#e65100;">Archivé</span>
                            @else
                                <span class="badge bg-danger">Obsolète</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-muted">Réservation</span>
                            @if($document->isCheckedOut())
                                <span class="badge" style="background:#fff3e0;color:#e65100;"><i class="bi bi-lock-fill me-1"></i>Réservé</span>
                            @else
                                <span class="badge bg-light text-dark border"><i class="bi bi-unlock me-1"></i>Libre</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-muted">Signature</span>
                            @if($document->signature_status === 'signed')
                                <span class="badge" style="background:#e6f4ea;color:#188038;"><i class="bi bi-patch-check-fill me-1"></i>Signé</span>
                            @elseif($document->signature_status === 'pending')
                                <span class="badge" style="background:#fff3e0;color:#e65100;">En attente</span>
                            @elseif($document->signature_status === 'rejected')
                                <span class="badge bg-danger">Rejetée</span>
                            @else
                                <span class="badge bg-light text-dark border">Non signé</span>
                            @endif
                        </div>
                        @if($document->requires_approval)
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-muted">Approbation</span>
                            @if($document->approved_at)
                                <span class="badge" style="background:#e6f4ea;color:#188038;"><i class="bi bi-check2-circle me-1"></i>Approuvé</span>
                            @else
                                <span class="badge" style="background:#fff3e0;color:#e65100;"><i class="bi bi-clock me-1"></i>En attente</span>
                            @endif
                        </div>
                        @endif
                        @if($document->requires_approval && !$document->approved_at && $document->is_current_version)
                        <form action="{{ route('documents.approve', $document) }}" method="POST" class="mt-1">
                            @csrf
                            <button type="submit" class="btn btn-outline-success btn-sm w-100">
                                <i class="bi bi-check-circle me-1"></i>Approuver
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Checkout & Signature partials --}}
            @include('repositories.documents.partials.checkout')
            @include('repositories.documents.partials.signature')
            @include('repositories.documents.partials.workflow')

            {{-- ===== STATS ===== --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header py-2" style="background:#fafbfc;border-bottom:1px solid #eef0f3;">
                    <span class="fw-semibold" style="font-size:.83rem;"><i class="bi bi-bar-chart me-2 text-secondary"></i>Statistiques</span>
                </div>
                <div class="card-body px-3 py-2" style="font-size:.78rem;">
                    <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Consultations</span><strong>{{ $document->download_count ?? 0 }}</strong></div>
                    @if($document->last_viewed_at)
                    <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Dernière vue</span><span>{{ $document->last_viewed_at->format('d/m/Y') }}</span></div>
                    @endif
                </div>
            </div>

        </div>
    </div>{{-- /d-flex --}}
</div>

{{-- ===== MODAL: Nouvelle version ===== --}}
<div class="modal fade" id="uploadVersionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('documents.upload', $document) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header py-2">
                    <h6 class="modal-title"><i class="bi bi-upload me-2 text-success"></i>Nouvelle version</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Fichier <span class="text-danger">*</span></label>
                        <input type="file" class="form-control form-control-sm" name="file" required>
                        <div class="form-text">Max 50 MB</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-semibold">Notes de version</label>
                        <textarea class="form-control form-control-sm" name="version_notes" rows="3" placeholder="Décrivez les changements..."></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-upload me-1"></i>Téléverser</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== MODAL: Supprimer ===== --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('documents.destroy', $document) }}" method="POST">
                @csrf @method('DELETE')
                <div class="modal-header py-2 bg-danger text-white">
                    <h6 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Confirmer la suppression</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Supprimer <strong>{{ $document->name }}</strong> et <strong>toutes ses versions</strong> ?</p>
                    <div class="alert alert-danger py-2 mb-0" style="font-size:.82rem;">
                        <i class="bi bi-info-circle me-1"></i>Cette action est irréversible.
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash me-1"></i>Supprimer définitivement</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.gd-doc-view .gd-doc-chip {
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
}
.gd-doc-view .gd-preview-toolbar {
    font-size: .8rem;
}
.gd-ver-current {
    background: #f0f4ff;
}
</style>
@endpush
