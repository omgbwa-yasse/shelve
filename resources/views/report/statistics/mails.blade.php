@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1 class="mb-4">Statistiques du module Courrier</h1>

        <div class="row">
            <!-- Statistiques générales des courriers -->
            <div class="col-md-3 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Aperçu général</h5>
                        <p class="card-text">Total des courriers: {{ $totalMails }}</p>
                        <p class="card-text">Courriers envoyés: {{ $sentMails }}</p>
                        <p class="card-text">Courriers reçus: {{ $receivedMails }}</p>
                        <p class="card-text">Courriers en cours: {{ $inProgressMails }}</p>
                    </div>
                </div>
            </div>

            <!-- Statistiques par priorité -->
            <div class="col-md-3 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Courriers par priorité</h5>
                        <div style="height: 300px;">
                            <canvas id="mailsPriorityChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistiques par typologie -->
            <div class="col-md-3 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Courriers par typologie</h5>
                        <div style="height: 300px;">
                            <canvas id="mailsTypologyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Temps moyen de traitement -->
            <div class="col-md-3 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Temps moyen de traitement</h5>
                        <p class="card-text">Temps moyen global: {{ number_format($averageProcessingTime, 1) }} jours</p>
                        <div style="height: 250px;">
                            <canvas id="processingTimeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Évolution du nombre de courriers -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Évolution du nombre de courriers</h5>
                        <div style="height: 300px;">
                            <canvas id="mailsEvolutionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Distribution mensuelle des courriers -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Distribution mensuelle des courriers</h5>
                        <div style="height: 300px;">
                            <canvas id="monthlyDistributionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Top 5 des organisations -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Top 5 des organisations</h5>
                        <div style="height: 300px;">
                            <canvas id="topOrganisationsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top 10 des auteurs -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Top 10 des auteurs</h5>
                        <div style="height: 300px;">
                            <canvas id="topAuthorsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Statistiques des pièces jointes -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Pièces jointes</h5>
                        <p class="card-text">Nombre total de pièces jointes: {{ $totalAttachments }}</p>
                        <p class="card-text">Taille moyenne des pièces jointes: {{ number_format($averageAttachmentSize, 2) }} MB</p>
                        <div style="height: 250px;">
                            <canvas id="attachmentTypeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions sur les courriers -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Actions sur les courriers</h5>
                        <div style="height: 300px;">
                            <canvas id="mailActionsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const generateRandomColors = (count) => {
                const colors = [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                    '#FF9F40', '#15A085', '#E74C3C', '#8E44AD', '#2C3E50'
                ];
                return count <= colors.length ? colors.slice(0, count) :
                    [...colors, ...Array.from({length: count - colors.length}, () =>
                        '#' + Math.floor(Math.random()*16777215).toString(16).padStart(6, '0')
                    )];
            };

            const defaultOptions = {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            };

            function createChart(id, type, rawLabels, rawData, customOptions = {}) {
                const canvas = document.getElementById(id);
                if (!canvas) {
                    console.error(`Canvas ${id} not found`);
                    return;
                }

                // Convertir les objets en tableaux si nécessaire
                const labels = typeof rawLabels === 'object' ? Object.values(rawLabels) : rawLabels;
                const data = Array.isArray(rawData) ? rawData : Object.values(rawData);

                const colors = generateRandomColors(data.length);

                new Chart(canvas, {
                    type: type,
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: colors,
                            borderColor: type === 'line' ? colors[0] : colors,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        ...defaultOptions,
                        ...customOptions
                    }
                });
            }

            // Création des graphiques
            createChart('mailsPriorityChart', 'pie', @json($mailsPriorityLabels), @json(array_values($mailsPriorityData)));
            createChart('mailsTypologyChart', 'doughnut', @json($mailsTypologyLabels), @json(array_values($mailsTypologyData)));
            createChart('mailsEvolutionChart', 'line', @json($mailsEvolutionLabels), @json($mailsEvolutionData), {
                scales: {
                    y: { beginAtZero: true }
                }
            });
            createChart('topOrganisationsChart', 'bar', @json($topOrganisationsLabels), @json($topOrganisationsData), {
                indexAxis: 'y'
            });
            createChart('processingTimeChart', 'bar', @json($processingTimeLabels), @json($processingTimeData));
            createChart('attachmentTypeChart', 'pie', @json($attachmentTypeLabels), @json(array_values($attachmentTypeData)));
            createChart('monthlyDistributionChart', 'bar', @json($monthlyDistributionLabels), @json($monthlyDistributionData));
            createChart('mailActionsChart', 'bar', @json($mailActionsLabels), @json($mailActionsData), {
                indexAxis: 'y'
            });
            createChart('topAuthorsChart', 'bar', @json($topSendersLabels), @json($topSendersData), {
                indexAxis: 'y'
            });
        });
    </script>
@endsection
