<div class="import-preview">
    <div class="row mb-3">
        <div class="col-md-6">
            <h5>{{ __('Fichier') }}: {{ $filename }}</h5>
            <p><strong>{{ __('Format') }}:</strong> {{ strtoupper($format) }}</p>
            <p><strong>{{ __('Mode d\'import') }}:</strong>
                @switch($mode)
                    @case('add')
                        {{ __('Ajouter uniquement') }}
                        @break
                    @case('update')
                        {{ __('Mettre à jour uniquement') }}
                        @break
                    @case('replace')
                        {{ __('Remplacer tout') }}
                        @break
                    @case('merge')
                        {{ __('Fusionner') }}
                        @break
                @endswitch
            </p>
        </div>
        <div class="col-md-6">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>{{ __('Estimation') }}:</strong> {{ $data['estimated_terms'] ?? 0 }} {{ __('termes à traiter') }}
            </div>
        </div>
    </div>

    @if($format === 'csv')
        <h6>{{ __('Structure du fichier CSV') }}</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        @foreach($data['header'] as $column)
                            <th>{{ $column }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['sample_data'] as $row)
                        <tr>
                            @foreach($data['header'] as $column)
                                <td>{{ $row[$column] ?? '' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-muted small">{{ __('Aperçu des premières lignes. Total de lignes') }}: {{ $data['total_lines'] }}</p>

    @elseif($format === 'skos')
        <h6>{{ __('Concepts SKOS détectés') }}</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Label préféré') }}</th>
                        <th>{{ __('Labels alternatifs') }}</th>
                        <th>{{ __('URI') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['sample_data'] as $concept)
                        <tr>
                            <td>{{ $concept['preferred_label'] }}</td>
                            <td>
                                @if(!empty($concept['alternative_labels']))
                                    @foreach($concept['alternative_labels'] as $altLabel)
                                        <span class="badge bg-secondary me-1">{{ $altLabel }}</span>
                                    @endforeach
                                @endif
                            </td>
                            <td><small>{{ $concept['about'] }}</small></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-muted small">{{ __('Aperçu des premiers concepts. Total de concepts') }}: {{ $data['total_concepts'] }}</p>

    @elseif($format === 'rdf')
        <h6>{{ __('Ressources RDF détectées') }}</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Label') }}</th>
                        <th>{{ __('Commentaire') }}</th>
                        <th>{{ __('URI') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['sample_data'] as $resource)
                        <tr>
                            <td>{{ $resource['label'] }}</td>
                            <td>{{ $resource['comment'] }}</td>
                            <td><small>{{ $resource['about'] }}</small></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-muted small">{{ __('Aperçu des premières ressources. Total de descriptions') }}: {{ $data['total_descriptions'] }}</p>
    @endif

    @if($mode === 'replace')
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>{{ __('Attention') }}:</strong> {{ __('Le mode "Remplacer tout" supprimera définitivement tous les termes existants dans le thésaurus avant d\'importer les nouveaux données.') }}
        </div>
    @endif

    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        {{ __('Vérifiez les données ci-dessus avant de confirmer l\'import. Cette opération peut prendre quelques minutes selon la taille du fichier.') }}
    </div>
</div>
