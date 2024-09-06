@extends('layouts.app')

@section('content')
    <div class="container-fluid  bg-light">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <header class="text-center mb-5">
                    <h1 class="display-4">Bienvenue dans {{ config('app.name') }}</h1>

                    <h1 class="display-4 fw-bold text-primary bg-light">Système d'Archivage Électronique (SAE)</h1>
                    <p class="lead text-muted">Votre solution complète pour la gestion, la conservation et la recherche de documents d'archives</p>
                </header>

                <div class="row g-4">
                    @php
                        $modules = [
                            ['title' => 'Courrier', 'icon' => 'bi-envelope', 'description' => 'Gestion des flux de correspondance, de leur réception à leur archivage final.'],
                            ['title' => 'Répertoire', 'icon' => 'bi-folder2-open', 'description' => 'Indexation et classification des documents selon un plan de classement précis.'],
                            ['title' => 'Demande', 'icon' => 'bi-search', 'description' => "Gestion des demandes d'accès aux documents d'archives avec recherche avancée."],
                            ['title' => 'Transfert', 'icon' => 'bi-arrow-left-right', 'description' => 'Versement  de documents dans le SAE avec traçabilité complète.'],
                            ['title' => 'Tâches', 'icon' => 'bi-list-check', 'description' => 'Gestion des tâches liées au cycle de vie des documents archivés.'],
                            ['title' => 'Dépôt', 'icon' => 'bi-archive', 'description' => 'Conservation à long terme des documents, garantissant authenticité et intégrité.'],
                            ['title' => 'Outils de gestion', 'icon' => 'bi-gear', 'description' => '.'],
                            ['title' => 'Chariots', 'icon' => 'bi-cart3', 'description' => 'Gestion des mouvements physiques des archives avec suivi rigoureux.'],
                            ['title' => 'Paramètres', 'icon' => 'bi-sliders', 'description' => 'Configuration du système.']
                        ];
                    @endphp

                    @foreach($modules as $module)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi {{ $module['icon'] }} fs-4 text-primary me-3"></i>
                                        <h3 class="h5 card-title mb-0">{{ $module['title'] }}</h3>
                                    </div>
                                    <p class="card-text text-muted">{{ $module['description'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
        .card {
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }
    </style>
@endpush
