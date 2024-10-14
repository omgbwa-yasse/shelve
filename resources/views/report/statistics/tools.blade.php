@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1 class="mb-4">Statistiques du module Outils</h1>

        <div class="row">
            <!-- Plan de classement -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Plan de classement</h5>
                        <p>Total des activités : {{ $totalActivities }}</p>
                        <p>Activités de premier niveau : {{ $topLevelActivities }}</p>
                        <p>Activités avec communicabilité : {{ $activitiesWithCommunicability }}</p>
                    </div>
                </div>
            </div>

            <!-- Référentiel de conservation -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Référentiel de conservation</h5>
                        <p>Total des règles : {{ $totalRetentions }}</p>
                        <p>Durée moyenne : {{ number_format($averageRetentionDuration, 2) }} ans</p>
                        <canvas id="retentionsBySortChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Communicabilités -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Communicabilités</h5>
                        <p>Total des règles : {{ $totalCommunicabilities }}</p>
                        <p>Durée moyenne : {{ number_format($averageCommunicabilityDuration, 2) }} ans</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Lois et Articles -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Lois et Articles</h5>
                        <p>Total des lois : {{ $totalLaws }}</p>
                        <p>Total des articles : {{ $totalLawArticles }}</p>
                    </div>
                </div>
            </div>

            <!-- Organigramme -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Organigramme</h5>
                        <p>Total des organisations : {{ $totalOrganisations }}</p>
                        <p>Organisations de premier niveau : {{ $topLevelOrganisations }}</p>
                    </div>
                </div>
            </div>

            <!-- Thésaurus -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Thésaurus</h5>
                        <p>Total des termes : {{ $totalTerms }}</p>
                        <canvas id="termsByCategoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Termes par langue -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Termes par langue</h5>
                        <canvas id="termsByLanguageChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const generateRandomColors = (count) => {
            return Array.from({length: count}, () =>
                '#' + Math.floor(Math.random()*16777215).toString(16).padStart(6, '0')
            );
        };

        const createChart = (id, type, labels, data, options = {}) => {
            new Chart(document.getElementById(id).getContext('2d'), {
                type: type,
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Nombre',
                        data: data,
                        backgroundColor: generateRandomColors(labels.length),
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    },
                    ...options
                }
            });
        };

        // Création des graphiques
        createChart('retentionsBySortChart', 'pie',
            @json(array_values($sortNames)),
            @json(array_values($retentionsBySort))
        );

        createChart('termsByCategoryChart', 'bar',
            @json(array_values($categoryNames)),
            @json(array_values($termsByCategory)),
            {
                scales: { y: { beginAtZero: true } }
            }
        );

        createChart('termsByLanguageChart', 'bar',
            @json(array_values($languageNames)),
            @json(array_values($termsByLanguage)),
            {
                scales: { y: { beginAtZero: true } }
            }
        );
    </script>
@endsection
