@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('records.index') }}">Archives</a></li>
                        @foreach($record->getAncestors()->reverse() as $ancestor)
                            <li class="breadcrumb-item">
                                <a href="{{ route('records.show', $ancestor) }}">{{ $ancestor->code }} — {{ $ancestor->name }}</a>
                            </li>
                        @endforeach
                        <li class="breadcrumb-item active">{{ $record->code }}</li>
                    </ol>
                </nav>
                <h1>{{ $record->code }} — {{ $record->name }}</h1>
                <div>
                    <span class="badge {{ $record->isContainer() ? 'bg-success' : 'bg-info' }}">{{ $record->type?->name ?? '—' }}</span>
                    <span class="badge bg-light text-dark">{{ $record->level?->name ?? '—' }}</span>
                    <span class="badge bg-light text-dark">{{ $record->status?->name ?? '—' }}</span>
                    @if($record->version_number > 1)
                        <span class="badge bg-warning text-dark">version {{ $record->version_number }}</span>
                    @endif
                    @if($record->is_essential)
                        <span class="badge bg-danger">Document essentiel</span>
                    @endif
                </div>
            </div>
            <div class="d-flex gap-2">
                @can('records_update')
                    <a href="{{ route('records.edit', $record) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i> Modifier</a>
                    <form method="POST" action="{{ route('records.create-version', $record) }}">
                        @csrf
                        <button class="btn btn-outline-warning"><i class="bi bi-files"></i> Nouvelle version</button>
                    </form>
                    <form method="POST" action="{{ route('records.destroy', $record) }}"
                          onsubmit="return confirm('Supprimer cette notice ?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-outline-danger"><i class="bi bi-trash"></i> Supprimer</button>
                    </form>
                @endcan
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        @if(!$record->is_current_version)
            <div class="alert alert-warning">
                <i class="bi bi-info-circle"></i> Cette notice est une <strong>ancienne version</strong>.
                <a href="{{ route('records.versions', $record) }}">Voir l'historique</a>.
            </div>
        @endif

        <div class="row g-4">
            {{-- Description --}}
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header"><i class="bi bi-info-circle"></i> Description</div>
                    <div class="card-body">
                        @if($record->description)
                            <p>{{ $record->description }}</p>
                        @endif
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Dates</dt>
                            <dd class="col-sm-8">
                                @if($record->date_exact) {{ $record->date_exact->format('d/m/Y') }}
                                @elseif($record->start_date || $record->end_date)
                                    {{ $record->start_date?->format('d/m/Y') ?? '…' }} — {{ $record->end_date?->format('d/m/Y') ?? '…' }}
                                @else —
                                @endif
                            </dd>
                            <dt class="col-sm-4">Activité</dt>
                            <dd class="col-sm-8">{{ $record->activity?->name ?? '—' }}</dd>
                            <dt class="col-sm-4">Organisation</dt>
                            <dd class="col-sm-8">{{ $record->organisation?->name ?? '—' }}</dd>
                            <dt class="col-sm-4">Créé par</dt>
                            <dd class="col-sm-8">{{ $record->creator?->name ?? '—' }} @if($record->created_at) ({{ $record->created_at->format('d/m/Y') }}) @endif</dd>
                            <dt class="col-sm-4">Confidentialité</dt>
                            <dd class="col-sm-8">{{ $record->confidentiality?->name ?? ($record->access_level ?? 'internal') }}</dd>
                            <dt class="col-sm-4">Cycle de vie</dt>
                            <dd class="col-sm-8">
                                @if($record->opening_date) Ouverture {{ $record->opening_date->format('d/m/Y') }}<br>@endif
                                @if($record->closing_date) Fermeture {{ $record->closing_date->format('d/m/Y') }}<br>@endif
                                @if($record->transfer_effective_date) Transfert {{ $record->transfer_effective_date->format('d/m/Y') }}<br>@endif
                                @if($record->deposit_effective_date) Versement {{ $record->deposit_effective_date->format('d/m/Y') }}<br>@endif
                                @if($record->destruction_effective_date) Destruction {{ $record->destruction_effective_date->format('d/m/Y') }}@endif
                            </dd>
                            <dt class="col-sm-4">Auteurs</dt>
                            <dd class="col-sm-8">
                                @forelse($record->authors as $author)
                                    <span class="badge bg-light text-dark">{{ $author->name ?? $author->last_name ?? '' }}</span>
                                @empty —
                                @endforelse
                            </dd>
                            <dt class="col-sm-4">Termes</dt>
                            <dd class="col-sm-8">
                                @forelse($record->thesaurusConcepts as $concept)
                                    <span class="badge bg-secondary">{{ $concept->preferred_label }}</span>
                                @empty —
                                @endforelse
                            </dd>
                            <dt class="col-sm-4">Mots-clés</dt>
                            <dd class="col-sm-8">
                                @forelse($record->keywords as $keyword)
                                    <span class="badge bg-secondary">{{ $keyword->name }}</span>
                                @empty —
                                @endforelse
                            </dd>
                        </dl>

                        @if(!empty($record->metadata))
                            <hr>
                            <h6>Métadonnées du type</h6>
                            <dl class="row mb-0">
                                @foreach($record->metadata as $code => $value)
                                    <dt class="col-sm-4">{{ $code }}</dt>
                                    <dd class="col-sm-8">{{ is_array($value) ? json_encode($value) : $value }}</dd>
                                @endforeach
                            </dl>
                        @endif

                        @foreach(['content', 'archival_history', 'biographical_history', 'note', 'archivist_note', 'access_conditions', 'reproduction_conditions'] as $section)
                            @if($record->{$section})
                                <hr>
                                <h6>{{ ucfirst(str_replace('_', ' ', $section)) }}</h6>
                                <p class="mb-0">{{ $record->{$section} }}</p>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Enfants --}}
                @if($record->children->isNotEmpty())
                    <div class="card mb-4">
                        <div class="card-header"><i class="bi bi-diagram-3"></i> Sous-notices ({{ $record->children->count() }})</div>
                        <ul class="list-group list-group-flush">
                            @foreach($record->children as $child)
                                <li class="list-group-item d-flex justify-content-between">
                                    <a href="{{ route('records.show', $child) }}">{{ $child->code }} — {{ $child->name }}</a>
                                    <span class="badge bg-light text-dark">{{ $child->type?->name ?? '' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Versions --}}
                @include('records.partials.version-history', ['record' => $record])
            </div>

            {{-- Supports --}}
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-layers"></i> Supports ({{ $record->mediums->count() }})</span>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addMediumModal">
                            <i class="bi bi-plus-circle"></i> Ajouter
                        </button>
                    </div>
                    <div class="card-body">
                        @forelse($record->mediums as $medium)
                            @include('records.partials.medium-card', ['medium' => $medium, 'record' => $record])
                        @empty
                            <p class="text-muted mb-0">Aucun support rattaché. Un support peut être papier (boîte) et/ou numérique (fichier).</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('records_update')
        <div class="modal fade" id="addMediumModal" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('records.add-medium', $record) }}" enctype="multipart/form-data" class="modal-content">
                    @csrf
                    <div class="modal-header"><h5 class="modal-title">Ajouter un support</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Type de support</label>
                            <select name="support_id" class="form-select" required>
                                @foreach(\App\Models\RecordSupport::all() as $support)
                                    <option value="{{ $support->id }}">{{ $support->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Boîte / contenant (support papier)</label>
                            <select name="container_id" class="form-select">
                                <option value="">— Aucun —</option>
                                @foreach(\App\Models\Container::all() as $container)
                                    <option value="{{ $container->id }}">{{ $container->code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fichier (support numérique)</label>
                            <input type="file" name="attachment" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    @endcan
@endsection
