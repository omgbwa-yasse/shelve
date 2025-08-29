@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="container">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <h1> Ajouter un document </h1>
            <form action="{{ route('slips.records.update', [$slip, $slipRecord ] ) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    Versement : <h3>{{ $slip->code }} - {{ $slip->name }}</h3>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="code" class="form-label">Code</label>
                        <input type="text" class="form-control" id="code" name="code" value="{{ $slipRecord->code }}" required maxlength="10">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" value="{{ $slipRecord->name }}" name="name" required>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="date_start" class="form-label">Date Start</label>
                        <input type="text" class="form-control" id="date_start" name="date_start" value="{{ $slipRecord->date_start }}" maxlength="10">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="date_end" class="form-label">Date End</label>
                        <input type="text" class="form-control" id="date_end" name="date_end" value="{{ $slipRecord->date_end }}" maxlength="10">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="date_exact" class="form-label">Date Exact</label>
                        <input type="date" class="form-control" id="date_exact" value="{{ $slipRecord->date_exact }}" name="date_exact">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="content" class="form-label">Content</label>
                    <textarea class="form-control" id="content" name="content" value="{{ $slipRecord->content }}"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="level_id" class="form-label">Niveau de description</label>
                        <select class="form-select" id="level_id" name="level_id" required>
                            @foreach ($levels as $level)
                                <option value="{{ $level->id }}">{{ $level->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="width" class="form-label">Width</label>
                        <input type="number" class="form-control" id="width" name="width" value="{{ $slipRecord->width }}" step="0.01">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="width_description" class="form-label">Width Description</label>
                        <input type="text" class="form-control" id="width_description" name="width_description" value="{{ $slipRecord->width_description }}" maxlength="100">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="support_id" class="form-label">Support de conservation</label>
                        <select class="form-select" id="support_id" name="support_id" required>
                            @foreach ($supports as $support)
                                <option value="{{ $support->id }}" {{ $support->id == $slipRecord->support_id ? 'selected' : '' }}>{{ $support->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label for="activity_id" class="form-label">Activité</label>
                        <select class="form-select" id="activity_id" name="activity_id" required>
                            @foreach ($activities as $activity)
                                <option value="{{ $activity->id }}" {{ $activity->id == $slipRecord->activity_id ? 'selected' : '' }}>{{ $activity->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Gestion des contenants -->
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">
                            <i class="bi bi-box me-1"></i>Contenants associés
                        </label>

                        <!-- Contenants actuellement associés -->
                        <div id="current-containers" class="mb-3">
                            @if($slipRecord->containers->isNotEmpty())
                                <div class="alert alert-info">
                                    <strong>Contenants actuels:</strong>
                                    @foreach($slipRecord->containers as $container)
                                        <span class="badge bg-primary me-1">{{ $container->code }}</span>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Aucun contenant associé
                                </div>
                            @endif
                        </div>

                        <!-- Sélection de nouveaux contenants -->
                        <div class="card border-light">
                            <div class="card-body">
                                <h6 class="card-title">Associer à des contenants</h6>
                                <select class="form-select" id="container_ids" name="container_ids[]" multiple>
                                    @foreach ($containers as $container)
                                        <option value="{{ $container->id }}"
                                                {{ $slipRecord->containers->contains($container->id) ? 'selected' : '' }}>
                                            {{ $container->code }} - {{ $container->description ?? 'Sans description' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">
                                    <i class="bi bi-info-circle"></i>
                                    Maintenez Ctrl (Cmd sur Mac) pour sélectionner plusieurs contenants.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Keywords Field -->
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="keywords" class="form-label">
                            <i class="fas fa-tags me-1"></i>Mots-clés
                        </label>
                        <input type="text"
                               id="keywords"
                               name="keywords"
                               class="form-control"
                               value="{{ old('keywords', $slipRecord->keywords_string ?? '') }}"
                               placeholder="Entrez les mots-clés séparés par des points-virgules (;)">
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i>
                            Séparez les mots-clés par des points-virgules. Les nouveaux mots-clés seront créés automatiquement.
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Mettre à jour</button>
            </form>
        </div>

        <!-- Include keyword manager script -->
        <script src="{{ asset('js/keyword-manager.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof KeywordManager !== 'undefined') {
                    new KeywordManager('#keywords', {
                        searchUrl: '{{ route("keywords.search") }}',
                        minChars: 2,
                        debounceDelay: 300
                    });
                }
            });
        </script>

@endsection
