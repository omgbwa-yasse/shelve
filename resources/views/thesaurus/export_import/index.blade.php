@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">{{ __('Thésaurus - Import/Export') }}</h1>

            <div class="row">
                <!-- Section Export -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ __('Export du thésaurus') }}</h4>
                        </div>
                        <div class="card-body">
                            <p>{{ __('Exportez votre thésaurus dans différents formats pour le partager ou l\'archiver.') }}</p>

                            <div class="d-grid gap-3">
                                <a href="{{ route('thesaurus.export.skos') }}" class="btn btn-primary">
                                    <i class="bi bi-file-earmark-code"></i> {{ __('Exporter au format SKOS (XML)') }}
                                </a>
                                <a href="{{ route('thesaurus.export.csv') }}" class="btn btn-primary">
                                    <i class="bi bi-file-earmark-spreadsheet"></i> {{ __('Exporter au format CSV') }}
                                </a>
                                <a href="{{ route('thesaurus.export.rdf') }}" class="btn btn-primary">
                                    <i class="bi bi-file-earmark-code"></i> {{ __('Exporter au format RDF (XML)') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Import -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ __('Import du thésaurus') }}</h4>
                        </div>
                        <div class="card-body">
                            <p>{{ __('Importez un thésaurus existant à partir d\'un fichier externe.') }}</p>

                            <div class="d-grid gap-3">
                                <a href="{{ route('thesaurus.import.skos.form') }}" class="btn btn-success">
                                    <i class="bi bi-cloud-arrow-up"></i> {{ __('Importer depuis un fichier SKOS (XML)') }}
                                </a>
                                <a href="{{ route('thesaurus.import.csv.form') }}" class="btn btn-success">
                                    <i class="bi bi-cloud-arrow-up"></i> {{ __('Importer depuis un fichier CSV') }}
                                </a>
                                <a href="{{ route('thesaurus.import.rdf.form') }}" class="btn btn-success">
                                    <i class="bi bi-cloud-arrow-up"></i> {{ __('Importer depuis un fichier RDF') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Information et aide -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ __('Informations') }}</h4>
                        </div>
                        <div class="card-body">
                            <h5>{{ __('Format SKOS') }}</h5>
                            <p>{{ __('Le format SKOS (Simple Knowledge Organization System) est une norme du W3C pour la représentation de thésaurus, de taxonomies et d\'autres types de vocabulaires contrôlés.') }}</p>

                            <h5>{{ __('Format CSV') }}</h5>
                            <p>{{ __('Le format CSV permet un import/export simple via des tableurs comme Excel ou LibreOffice Calc.') }}</p>

                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                {{ __('Attention : L\'import de données remplacera ou fusionnera avec votre thésaurus existant selon les options choisies. Il est recommandé de faire une sauvegarde avant d\'importer des données.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
