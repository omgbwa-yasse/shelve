@extends('layouts.app')

@section('title', __('Résultats de l\'analyse IA'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-robot"></i>
                        {{ __('Résultats de l\'analyse IA') }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('records.select-attachments') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i>
                            {{ __('Nouvelle analyse') }}
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if(isset($analysis['success']) && $analysis['success'])

                    <!-- Indicateur de confiance -->
                    <div class="alert {{ $analysis['result']['record']['confidence'] > 0.7 ? 'alert-success' : ($analysis['result']['record']['confidence'] > 0.4 ? 'alert-warning' : 'alert-danger') }}">
                        <h5><i class="icon fas fa-chart-line"></i> {{ __('Niveau de confiance') }}</h5>
                        <div class="progress mb-2">
                            <div class="progress-bar"
                                 role="progressbar"
                                 style="width: {{ ($analysis['result']['record']['confidence'] * 100) }}%"
                                 aria-valuenow="{{ ($analysis['result']['record']['confidence'] * 100) }}"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                                {{ number_format($analysis['result']['record']['confidence'] * 100, 1) }}%
                            </div>
                        </div>
                        @if($analysis['result']['record']['confidence'] < 0.5)
                            <p><strong>{{ __('Confiance faible') }}:</strong> {{ __('Révision manuelle fortement recommandée') }}</p>
                        @elseif($analysis['result']['record']['confidence'] < 0.7)
                            <p><strong>{{ __('Confiance moyenne') }}:</strong> {{ __('Vérifiez les détails avant validation') }}</p>
                        @else
                            <p><strong>{{ __('Confiance élevée') }}:</strong> {{ __('Les suggestions semblent fiables') }}</p>
                        @endif
                    </div>

                    <!-- Statistiques de traitement -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-file"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('Documents analysés') }}</span>
                                    <span class="info-box-number">{{ $analysis['result']['processingStats']['documentsCount'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('Extractions réussies') }}</span>
                                    <span class="info-box-number">{{ $analysis['result']['processingStats']['successfulExtractions'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('Temps de traitement') }}</span>
                                    <span class="info-box-number">{{ number_format($analysis['result']['processingStats']['processingTimeMs'] / 1000, 1) }}s</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fas fa-tags"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('Termes suggérés') }}</span>
                                    <span class="info-box-number">{{ count($analysis['result']['indexing']['suggestedTerms'] ?? []) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('records.create-from-ai') }}" method="POST" id="create-record-form">
                        @csrf
                        <input type="hidden" name="attachment_ids" value="{{ json_encode($attachments->pluck('id')) }}">

                        <!-- Navigation par onglets -->
                        <ul class="nav nav-tabs" id="analysis-tabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="record-tab" data-bs-toggle="tab" data-bs-target="#record" type="button" role="tab">
                                    <i class="fas fa-archive"></i> {{ __('Description du record') }}
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="indexing-tab" data-bs-toggle="tab" data-bs-target="#indexing" type="button" role="tab">
                                    <i class="fas fa-tags"></i> {{ __('Indexation thésaurus') }}
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">
                                    <i class="fas fa-file-alt"></i> {{ __('Documents analysés') }}
                                </button>
                            </li>
                            @if(isset($analysis['result']['recommendations']))
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="recommendations-tab" data-bs-toggle="tab" data-bs-target="#recommendations" type="button" role="tab">
                                    <i class="fas fa-lightbulb"></i> {{ __('Recommandations') }}
                                </button>
                            </li>
                            @endif
                        </ul>

                        <div class="tab-content mt-3" id="analysis-tabs-content">
                            <!-- Onglet Description du record -->
                            <div class="tab-pane fade show active" id="record" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="title" class="form-label">{{ __('Titre') }} <span class="text-danger">*</span></label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="title"
                                                   name="suggested_record[title]"
                                                   value="{{ $analysis['result']['record']['title'] ?? '' }}"
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label for="dateStart" class="form-label">{{ __('Date de début') }}</label>
                                            <input type="date"
                                                   class="form-control"
                                                   id="dateStart"
                                                   name="suggested_record[dateStart]"
                                                   value="{{ $analysis['result']['record']['dateStart'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label for="dateEnd" class="form-label">{{ __('Date de fin') }}</label>
                                            <input type="date"
                                                   class="form-control"
                                                   id="dateEnd"
                                                   name="suggested_record[dateEnd]"
                                                   value="{{ $analysis['result']['record']['dateEnd'] ?? '' }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="description" class="form-label">{{ __('Description') }}</label>
                                    <textarea class="form-control"
                                              id="description"
                                              name="suggested_record[description]"
                                              rows="4">{{ $analysis['result']['record']['description'] ?? '' }}</textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="scope" class="form-label">{{ __('Portée et contenu') }}</label>
                                            <textarea class="form-control"
                                                      id="scope"
                                                      name="suggested_record[scope]"
                                                      rows="3">{{ $analysis['result']['record']['scope'] ?? '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="arrangement" class="form-label">{{ __('Classement') }}</label>
                                            <textarea class="form-control"
                                                      id="arrangement"
                                                      name="suggested_record[arrangement]"
                                                      rows="3">{{ $analysis['result']['record']['arrangement'] ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="accessConditions" class="form-label">{{ __('Conditions d\'accès') }}</label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="accessConditions"
                                                   name="suggested_record[accessConditions]"
                                                   value="{{ $analysis['result']['record']['accessConditions'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="language" class="form-label">{{ __('Langue') }}</label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="language"
                                                   name="suggested_record[language]"
                                                   value="{{ $analysis['result']['record']['language'] ?? 'français' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="suggestedLevel" class="form-label">{{ __('Niveau suggéré') }}</label>
                                            <select class="form-control"
                                                    id="suggestedLevel"
                                                    name="suggested_record[suggestedLevel]">
                                                <option value="fonds" {{ ($analysis['result']['record']['suggestedLevel'] ?? '') === 'fonds' ? 'selected' : '' }}>{{ __('Fonds') }}</option>
                                                <option value="series" {{ ($analysis['result']['record']['suggestedLevel'] ?? '') === 'series' ? 'selected' : '' }}>{{ __('Série') }}</option>
                                                <option value="file" {{ ($analysis['result']['record']['suggestedLevel'] ?? '') === 'file' ? 'selected' : '' }}>{{ __('Dossier') }}</option>
                                                <option value="item" {{ ($analysis['result']['record']['suggestedLevel'] ?? '') === 'item' ? 'selected' : '' }}>{{ __('Pièce') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="notes" class="form-label">{{ __('Notes') }}</label>
                                    <textarea class="form-control"
                                              id="notes"
                                              name="suggested_record[notes]"
                                              rows="2">{{ $analysis['result']['record']['notes'] ?? '' }}</textarea>
                                </div>
                            </div>

                            <!-- Onglet Indexation thésaurus -->
                            <div class="tab-pane fade" id="indexing" role="tabpanel">
                                @if(isset($analysis['result']['indexing']['weightedTerms']) && count($analysis['result']['indexing']['weightedTerms']) > 0)
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    {{ __('L\'IA a proposé les termes suivants du thésaurus. Vous pouvez ajuster les poids et désélectionner les termes non pertinents.') }}
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="5%">{{ __('Inclure') }}</th>
                                                <th width="35%">{{ __('Terme') }}</th>
                                                <th width="15%">{{ __('Poids') }}</th>
                                                <th width="20%">{{ __('Contexte') }}</th>
                                                <th width="25%">{{ __('Justification') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($analysis['result']['indexing']['weightedTerms'] as $index => $weightedTerm)
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox"
                                                           name="suggested_indexing[weightedTerms][{{ $index }}][included]"
                                                           value="1"
                                                           checked
                                                           class="term-checkbox">
                                                </td>
                                                <td>
                                                    <strong>{{ $weightedTerm['termText'] ?? 'Terme #' . $weightedTerm['termId'] }}</strong>
                                                    <input type="hidden" name="suggested_indexing[weightedTerms][{{ $index }}][termId]" value="{{ $weightedTerm['termId'] }}">
                                                </td>
                                                <td>
                                                    <input type="range"
                                                           name="suggested_indexing[weightedTerms][{{ $index }}][weight]"
                                                           min="0.1"
                                                           max="1.0"
                                                           step="0.1"
                                                           value="{{ $weightedTerm['weight'] ?? 0.7 }}"
                                                           class="form-range weight-slider">
                                                    <small class="weight-display">{{ number_format($weightedTerm['weight'] ?? 0.7, 1) }}</small>
                                                </td>
                                                <td>
                                                    <select name="suggested_indexing[weightedTerms][{{ $index }}][context]" class="form-control form-control-sm">
                                                        <option value="terme principal" {{ ($weightedTerm['context'] ?? '') === 'terme principal' ? 'selected' : '' }}>{{ __('Principal') }}</option>
                                                        <option value="terme secondaire" {{ ($weightedTerm['context'] ?? '') === 'terme secondaire' ? 'selected' : '' }}>{{ __('Secondaire') }}</option>
                                                        <option value="terme connexe" {{ ($weightedTerm['context'] ?? '') === 'terme connexe' ? 'selected' : '' }}>{{ __('Connexe') }}</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ $weightedTerm['justification'] ?? __('Généré automatiquement') }}</small>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    {{ __('Aucun terme du thésaurus n\'a été trouvé. Vous pourrez ajouter une indexation manuellement après création du record.') }}
                                </div>
                                @endif
                            </div>

                            <!-- Onglet Documents analysés -->
                            <div class="tab-pane fade" id="documents" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>{{ __('Document') }}</th>
                                                <th>{{ __('Statut d\'extraction') }}</th>
                                                <th>{{ __('Contenu extrait') }}</th>
                                                <th>{{ __('Résumé') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($attachments as $attachment)
                                            @php
                                                $docAnalysis = collect($analysis['result']['documentAnalysis']['processedDocuments'] ?? [])->firstWhere('id', $attachment->id);
                                            @endphp
                                            <tr>
                                                <td>
                                                    <strong>{{ $attachment->name }}</strong>
                                                    <br><small class="text-muted">{{ \App\Helpers\FileHelper::formatBytes($attachment->size ?? 0) }}</small>
                                                </td>
                                                <td>
                                                    @if($docAnalysis && $docAnalysis['processingStatus'] === 'success')
                                                        <span class="badge bg-success">{{ __('Succès') }}</span>
                                                    @elseif($docAnalysis && $docAnalysis['processingStatus'] === 'extraction_failed')
                                                        <span class="badge bg-danger">{{ __('Échec extraction') }}</span>
                                                    @elseif($docAnalysis && $docAnalysis['processingStatus'] === 'unsupported_format')
                                                        <span class="badge bg-warning">{{ __('Format non supporté') }}</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ __('Non traité') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($docAnalysis && isset($docAnalysis['extractedLength']))
                                                        {{ number_format($docAnalysis['extractedLength']) }} {{ __('caractères') }}
                                                    @else
                                                        <span class="text-muted">{{ __('N/A') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ __('Inclus dans l\'analyse globale') }}</small>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @if(isset($analysis['result']['documentAnalysis']['summary']))
                                <div class="mt-3">
                                    <h5>{{ __('Résumé global des documents') }}</h5>
                                    <div class="alert alert-light">
                                        {{ $analysis['result']['documentAnalysis']['summary'] }}
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Onglet Recommandations -->
                            @if(isset($analysis['result']['recommendations']))
                            <div class="tab-pane fade" id="recommendations" role="tabpanel">
                                @if($analysis['result']['recommendations']['manualReview'])
                                <div class="alert alert-warning">
                                    <h5><i class="fas fa-exclamation-triangle"></i> {{ __('Révision manuelle recommandée') }}</h5>
                                    <p>{{ __('Le niveau de confiance est faible. Veuillez vérifier attentivement toutes les propositions avant validation.') }}</p>
                                </div>
                                @endif

                                @if(isset($analysis['result']['recommendations']['suggestedActions']) && count($analysis['result']['recommendations']['suggestedActions']) > 0)
                                <h5>{{ __('Actions suggérées') }}</h5>
                                <ul class="list-group">
                                    @foreach($analysis['result']['recommendations']['suggestedActions'] as $action)
                                    <li class="list-group-item">
                                        <i class="fas fa-arrow-right text-primary"></i>
                                        {{ $action }}
                                    </li>
                                    @endforeach
                                </ul>
                                @endif
                            </div>
                            @endif
                        </div>

                        <!-- Boutons d'action -->
                        <div class="mt-4 text-center">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-plus"></i>
                                {{ __('Créer le record avec ces propositions') }}
                            </button>
                            <a href="{{ route('records.select-attachments') }}" class="btn btn-secondary btn-lg ml-2">
                                <i class="fas fa-redo"></i>
                                {{ __('Nouvelle analyse') }}
                            </a>
                        </div>
                    </form>

                    @else
                    <div class="alert alert-danger">
                        <h5><i class="icon fas fa-ban"></i> {{ __('Erreur d\'analyse') }}</h5>
                        <p>{{ $analysis['message'] ?? __('Une erreur est survenue lors de l\'analyse des documents.') }}</p>
                        <a href="{{ route('records.select-attachments') }}" class="btn btn-primary">
                            {{ __('Réessayer') }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gérer les sliders de poids
    document.querySelectorAll('.weight-slider').forEach(slider => {
        const display = slider.parentElement.querySelector('.weight-display');

        slider.addEventListener('input', function() {
            display.textContent = parseFloat(this.value).toFixed(1);
        });
    });

    // Gérer les checkboxes de termes
    document.querySelectorAll('.term-checkbox').forEach(checkbox => {
        const row = checkbox.closest('tr');

        checkbox.addEventListener('change', function() {
            if (this.checked) {
                row.style.opacity = '1';
                row.querySelectorAll('input, select').forEach(input => input.disabled = false);
            } else {
                row.style.opacity = '0.5';
                row.querySelectorAll('input:not(.term-checkbox), select').forEach(input => input.disabled = true);
            }
        });
    });

    // Validation du formulaire
    document.getElementById('create-record-form').addEventListener('submit', function(e) {
        const title = document.getElementById('title').value.trim();
        if (!title) {
            e.preventDefault();
            alert('{{ __("Le titre est obligatoire") }}');
            document.getElementById('record-tab').click();
            document.getElementById('title').focus();
        }
    });


});
</script>

<style>
.weight-slider {
    width: 100%;
}

.weight-display {
    display: block;
    text-align: center;
    font-weight: bold;
    color: #007bff;
}

.tab-content {
    min-height: 400px;
}

.info-box {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border-radius: .25rem;
    background-color: #fff;
    display: flex;
    margin-bottom: 1rem;
}

.info-box-icon {
    border-radius: .25rem 0 0 .25rem;
    color: #fff;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 1.875rem;
    width: 90px;
}

.info-box-content {
    padding: .5rem .5rem .5rem .75rem;
    flex: 1;
}

.info-box-text {
    display: block;
    font-size: .875rem;
    color: #6c757d;
    text-transform: uppercase;
}

.info-box-number {
    display: block;
    font-weight: 700;
    font-size: 1.25rem;
}
</style>
@endsection
