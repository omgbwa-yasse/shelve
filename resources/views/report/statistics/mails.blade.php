@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1 class="mb-4">Statistiques du module Courrier</h1>

        <div class="row">
            <!-- Statistiques générales des courriers -->
            <div class="col-md-3 mb-4">
                <div class="card">
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
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Courriers par priorité</h5>
                        <canvas id="mailsPriorityChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Statistiques par type -->
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Courriers par type</h5>
                        <canvas id="mailsTypeChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Temps moyen de traitement -->
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Temps moyen de traitement</h5>
                        <p class="card-text">Temps moyen global: {{ number_format($averageProcessingTime, 1) }} jours</p>
                        <canvas id="processingTimeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Évolution du nombre de courriers -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Évolution du nombre de courriers</h5>
                        <canvas id="mailsEvolutionChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Distribution mensuelle des courriers -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Distribution mensuelle des courriers</h5>
                        <canvas id="monthlyDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Top 5 des organisations -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Top 5 des organisations</h5>
                        <canvas id="topOrganisationsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top 10 des auteurs -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Top 10 des auteurs</h5>
                        <canvas id="topAuthorsChart"></canvas>
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
                        <canvas id="attachmentTypeChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Actions sur les courriers -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Actions sur les courriers</h5>
                        <canvas id="mailActionsChart"></canvas>
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
            createChart('mailsPriorityChart', 'pie', @json($mailsPriorityLabels), @json(array_values($mailsPriorityData)));
            createChart('mailsTypeChart', 'doughnut', @json($mailsTypeLabels), @json(array_values($mailsTypeData)));
            createChart('mailsEvolutionChart', 'line', @json($mailsEvolutionLabels), @json($mailsEvolutionData), {
                scales: { y: { beginAtZero: true } }
            });
            createChart('topOrganisationsChart', 'bar', @json($topOrganisationsLabels), @json($topOrganisationsData), {
                scales: { y: { beginAtZero: true } }
            });
            createChart('processingTimeChart', 'bar', @json($processingTimeLabels), @json($processingTimeData), {
                scales: { y: { beginAtZero: true } }
            });
            createChart('attachmentTypeChart', 'pie', @json($attachmentTypeLabels), @json(array_values($attachmentTypeData)));
            createChart('monthlyDistributionChart', 'bar', @json($monthlyDistributionLabels), @json($monthlyDistributionData), {
                scales: { y: { beginAtZero: true } }
            });
            createChart('mailActionsChart', 'bar', @json($mailActionsLabels), @json($mailActionsData), {
                scales: { y: { beginAtZero: true } }
            });
            createChart('topAuthorsChart', 'bar', @json($topSendersLabels), @json($topSendersData), {
                scales: { y: { beginAtZero: true } }
            });
        </script>
@endsection
