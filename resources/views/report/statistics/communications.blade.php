@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1 class="mb-4">Statistiques du module Communication</h1>

        <div class="row">
            <!-- Statistiques générales des communications -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Aperçu général</h5>
                        <p class="card-text">Total des communications: {{ $totalCommunications }}</p>
                        <p class="card-text">Communications en attente: {{ $pendingCommunications }}</p>
                        <p class="card-text">Communications terminées: {{ $completedCommunications }}</p>
                        <p class="card-text">Temps moyen de retour: {{ $averageReturnTime }} jours</p>
                    </div>
                </div>
            </div>

            <!-- Communications par statut -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Communications par statut</h5>
                        <canvas id="communicationStatusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Distribution mensuelle des communications -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Distribution mensuelle</h5>
                        <canvas id="monthlyDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Évolution du nombre de communications -->
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Évolution du nombre de communications</h5>
                        <canvas id="communicationsEvolutionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Top 5 des utilisateurs demandeurs -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Top 5 des utilisateurs demandeurs</h5>
                        <canvas id="topUsersChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top 5 des organisations demandeuses -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Top 5 des organisations demandeuses</h5>
                        <canvas id="topOrganisationsChart"></canvas>
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
        createChart('communicationStatusChart', 'pie', @json(array_values($statusNames)), @json(array_values($communicationsByStatus)));
        createChart('communicationsEvolutionChart', 'line', @json($communicationsEvolutionLabels), @json($communicationsEvolutionData), {
            scales: { y: { beginAtZero: true } }
        });
        createChart('topUsersChart', 'bar', @json(array_values($topUsersLabels)), @json($topUsersData), {
            scales: { y: { beginAtZero: true } }
        });
        createChart('topOrganisationsChart', 'bar', @json(array_values($topOrganisationsLabels)), @json($topOrganisationsData), {
            scales: { y: { beginAtZero: true } }
        });
        createChart('monthlyDistributionChart', 'bar', @json($monthlyDistributionLabels), @json($monthlyDistributionData), {
            scales: { y: { beginAtZero: true } }
        });
    </script>
@endsection
