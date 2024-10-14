@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1 class="mb-4">Statistiques du module Transfert</h1>

        <div class="row">
            <!-- Statistiques générales des bordereaux -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Aperçu général</h5>
                        <p>Total des bordereaux : {{ $totalSlips }}</p>
                        <p>Bordereaux en attente : {{ $pendingSlips }}</p>
                        <p>Bordereaux approuvés : {{ $approvedSlips }}</p>
                        <p>Bordereaux intégrés : {{ $integratedSlips }}</p>
                    </div>
                </div>
            </div>

            <!-- Bordereaux par statut -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Bordereaux par statut</h5>
                        <canvas id="slipStatusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Statistiques des enregistrements -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Enregistrements</h5>
                        <p>Total des enregistrements : {{ $totalSlipRecords }}</p>
                        <p>Moyenne par bordereau : {{ number_format($averageRecordsPerSlip, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Évolution du nombre de bordereaux -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Évolution du nombre de bordereaux</h5>
                        <canvas id="slipsEvolutionChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Distribution mensuelle des bordereaux -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Distribution mensuelle des bordereaux</h5>
                        <canvas id="monthlyDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Top 5 des organisations de transfert -->
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Top 5 des organisations de transfert</h5>
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
        createChart('slipStatusChart', 'pie', @json(array_values($statusNames)), @json(array_values($slipsByStatus)));
        createChart('slipsEvolutionChart', 'line', @json($slipsEvolutionLabels), @json($slipsEvolutionData), {
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
