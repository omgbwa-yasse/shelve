@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1 class="mb-4">Statistiques du module Repository</h1>

        <div class="row">
            <!-- Statistiques générales -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Aperçu général</h5>
                        <p class="card-text">Total des records: {{ $totalRecords }}</p>
                        <p class="card-text">Records avec conteneur: {{ $recordsWithContainer }}</p>
                        <p class="card-text">Records sans conteneur: {{ $recordsWithoutContainer }}</p>
                    </div>
                </div>
            </div>

            <!-- Records par niveau de description -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Records par niveau de description</h5>
                        <canvas id="recordsByLevelChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Records par support -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Records par support</h5>
                        <canvas id="recordsBySupportChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Évolution du nombre de records -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Évolution du nombre de records</h5>
                        <canvas id="recordsEvolutionChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Distribution mensuelle des records -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Distribution mensuelle des records</h5>
                        <canvas id="monthlyDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Top 5 des activités -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Top 5 des activités liées aux records</h5>
                        <canvas id="topActivitiesChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Distribution des records par statut -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Distribution des records par statut</h5>
                        <canvas id="recordsByStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Nouvelles statistiques -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Statistiques supplémentaires</h5>
                        <p>Records avec auteurs: {{ $recordsWithAuthors }}</p>
                        <p>Records avec termes: {{ $recordsWithTerms }}</p>
                        <p>Records avec pièces jointes: {{ $recordsWithAttachments }}</p>
                        <p>Records avec enfants: {{ $recordsWithChildren }}</p>
                        <p>Moyenne d'enfants par record: {{ number_format($averageChildrenPerRecord, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Top 5 des organisations -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Top 5 des organisations</h5>
                        <canvas id="topOrganisationsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Statistiques des pièces jointes -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Pièces jointes</h5>
                        <p class="card-text">Nombre total de pièces jointes: {{ $totalAttachments }}</p>
                        <p class="card-text">Taille moyenne des pièces jointes: {{ number_format($averageAttachmentSize, 2) }} MB</p>
                        <canvas id="attachmentTypesChart"></canvas>
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
                        borderColor: type === 'line' ? 'rgb(75, 192, 192)' : undefined,
                        tension: type === 'line' ? 0.1 : undefined
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
        createChart('recordsByLevelChart', 'pie',
            @json(array_values($levelNames)),
            @json(array_values($recordsByLevel))
        );
        createChart('recordsBySupportChart', 'doughnut',
            @json(array_values($supportNames)),
            @json(array_values($recordsBySupport))
        );
        createChart('recordsEvolutionChart', 'line',
            @json($recordsEvolutionLabels),
            @json($recordsEvolutionData),
            { scales: { y: { beginAtZero: true } } }
        );
        createChart('topActivitiesChart', 'bar',
            @json($topActivities->pluck('activity_id')->map(function($id) use ($activityNames) { return $activityNames[$id] ?? 'Unknown'; })),
            @json($topActivities->pluck('count')),
            { scales: { y: { beginAtZero: true } } }
        );
        createChart('recordsByStatusChart', 'pie',
            @json(array_values($statusNames)),
            @json(array_values($recordsByStatus))
        );
        createChart('attachmentTypesChart', 'pie',
            @json(array_keys($attachmentTypes)),
            @json(array_values($attachmentTypes))
        );
        createChart('monthlyDistributionChart', 'bar',
            @json($monthlyDistributionLabels),
            @json($monthlyDistributionData),
            { scales: { y: { beginAtZero: true } } }
        );
        createChart('topOrganisationsChart', 'bar',
            @json(array_map(function($id) use ($organisationNames) { return $organisationNames[$id] ?? 'Unknown'; }, array_keys($topOrganisations))),
            @json(array_values($topOrganisations)),
            { scales: { y: { beginAtZero: true } } }
        );
    </script>
@endsection
