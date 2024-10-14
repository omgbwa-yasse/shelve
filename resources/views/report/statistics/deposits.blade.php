@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1 class="mb-4">Statistiques du module Dépôt</h1>

        <div class="row">
            <!-- Aperçu général -->
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Aperçu général</h5>
                        <p class="card-text">Total des bâtiments: {{ $totalBuildings }}</p>
                        <p class="card-text">Total des étages: {{ $totalFloors }}</p>
                        <p class="card-text">Total des salles: {{ $totalRooms }}</p>
                        <p class="card-text">Total des étagères: {{ $totalShelves }}</p>
                        <p class="card-text">Total des conteneurs: {{ $totalContainers }}</p>
                    </div>
                </div>
            </div>

            <!-- Distribution des conteneurs par statut -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Conteneurs par statut</h5>
                        <canvas id="containersByStatusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top 5 des bâtiments -->
            <div class="col-md-5 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Top 5 des bâtiments par nombre de conteneurs</h5>
                        <canvas id="topBuildingsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Utilisation moyenne des étagères -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Utilisation moyenne des étagères</h5>
                        <p class="card-text">Nombre moyen de conteneurs par étagère: {{ number_format($averageContainersPerShelf, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Distribution des salles par type -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Distribution des salles par type</h5>
                        <canvas id="roomsByTypeChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Évolution du nombre de conteneurs -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Évolution du nombre de conteneurs</h5>
                        <canvas id="containerEvolutionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Capacité totale vs utilisation réelle -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Capacité totale vs utilisation réelle</h5>
                        <canvas id="capacityUsageChart"></canvas>
                        <p class="mt-2">Capacité totale: {{ number_format($totalCapacity, 2) }} unités</p>
                        <p>Capacité utilisée: {{ number_format($usedCapacity, 2) }} unités</p>
                        <p>Taux d'utilisation: {{ number_format(($usedCapacity / $totalCapacity) * 100, 2) }}%</p>
                    </div>
                </div>
            </div>

            <!-- Top 5 des organisations créatrices de conteneurs -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Top 5 des organisations créatrices de conteneurs</h5>
                        <canvas id="topOrganisationsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Fonction pour générer des couleurs aléatoires
        const generateRandomColors = (count) => {
            return Array.from({length: count}, () => '#' + Math.floor(Math.random()*16777215).toString(16).padStart(6, '0'));
        };

        // Fonction pour créer un graphique
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
        createChart('containersByStatusChart', 'pie', @json(array_keys($containersByStatus)), @json(array_values($containersByStatus)));
        createChart('topBuildingsChart', 'bar',
            @json($topBuildings->pluck('name')),
            @json($topBuildings->map(function($building) {
                return $building->floors->sum(function($floor) {
                    return $floor->rooms->sum(function($room) {
                        return $room->shelves->sum('containers_count');
                    });
                });
            })),
            {
                scales: { y: { beginAtZero: true } },
                plugins: {
                    title: {
                        display: true,
                        text: 'Top 5 des bâtiments par nombre de conteneurs'
                    }
                }
            }
        );
        createChart('roomsByTypeChart', 'doughnut', @json(array_keys($roomsByType)), @json(array_values($roomsByType)));
        createChart('containerEvolutionChart', 'line', @json($containerEvolution->pluck('date')), @json($containerEvolution->pluck('count')), {
            scales: { y: { beginAtZero: true } }
        });
        createChart('capacityUsageChart', 'pie', ['Utilisé', 'Disponible'], [{{ $usedCapacity }}, {{ $totalCapacity - $usedCapacity }}]);
        createChart('topOrganisationsChart', 'bar', @json($topOrganisations->pluck('creator_organisation_id')), @json($topOrganisations->pluck('count')), {
            scales: { y: { beginAtZero: true } }
        });
    </script>
@endsection
