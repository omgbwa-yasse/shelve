@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1 class="mb-4">Statistiques du module Dolly</h1>

        <div class="row">
            <!-- Statistiques générales des dollies -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Aperçu général</h5>
                        <p class="card-text">Total des dollies: {{ $totalDollies }}</p>
                        <p class="card-text">Total des éléments: {{ $totalItems }}</p>
                        <p class="card-text">Moyenne d'éléments par dolly: {{ number_format($averageItemsPerDolly, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Statistiques par type de dolly -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Dollies par type</h5>
                        <canvas id="dolliesTypeChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Types d'éléments dans les dollies -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Types d'éléments dans les dollies</h5>
                        <canvas id="itemTypesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Évolution du nombre de dollies -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Évolution du nombre de dollies</h5>
                        <canvas id="dolliesEvolutionChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Distribution mensuelle des créations de dollies -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Distribution mensuelle des créations</h5>
                        <canvas id="monthlyDistributionChart"></canvas>
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
        createChart('dolliesTypeChart', 'pie', @json(array_values($dollyTypeLabels)), @json(array_values($dollyTypeData)));
        createChart('dolliesEvolutionChart', 'line', @json($dolliesEvolutionLabels), @json($dolliesEvolutionData), {
            scales: { y: { beginAtZero: true } }
        });
        createChart('itemTypesChart', 'pie', @json(array_keys($itemTypes)), @json(array_values($itemTypes)));
        createChart('monthlyDistributionChart', 'bar', @json($monthlyDistributionLabels), @json($monthlyDistributionData), {
            scales: { y: { beginAtZero: true } }
        });
    </script>
@endsection
