@extends('layouts.app')

@section('title', 'Tableau de bord des rapports')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Tableau de bord des rapports</h1>

    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Communications</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalCommunications }}</div>
                            <div class="text-xs font-weight-bold text-danger mb-1">En attente: {{ $pendingCommunications }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-chat-dots fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Mails</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalMails }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-envelope fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Documents</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalRecords }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-file-earmark-text fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Chariots (Dollies)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalDollies }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Communications au fil du temps</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="communicationsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Répartition des mails par priorité</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="mailsChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Répartition des bordereaux par statut</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="slipsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistiques générales</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered w-100">
                            <thead>
                                <tr>
                                    <th>Catégorie</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Utilisateurs</td>
                                    <td>{{ $totalUsers }}</td>
                                </tr>
                                <tr>
                                    <td>Organisations</td>
                                    <td>{{ $totalOrganisations }}</td>
                                </tr>
                                <tr>
                                    <td>Conteneurs</td>
                                    <td>{{ $totalContainers }}</td>
                                </tr>
                                <tr>
                                    <td>Bordereaux</td>
                                    <td>{{ $totalSlips }}</td>
                                </tr>
                                <tr>
                                    <td>Activités</td>
                                    <td>{{ $totalActivities }}</td>
                                </tr>
                                <tr>
                                    <td>Communicabilités</td>
                                    <td>{{ $totalCommunicabilities }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Répartition des chariots par type</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="dolliesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Communications Line Chart
    const communicationsDates = @json($communicationsDates);
    const communicationsData = @json($communicationsData);

    new Chart(document.getElementById('communicationsChart'), {
        type: 'line',
        data: {
            labels: communicationsDates,
            datasets: [{
                label: 'Communications',
                lineTension: 0.3,
                backgroundColor: "rgba(78, 115, 223, 0.05)",
                borderColor: "rgba(78, 115, 223, 1)",
                pointRadius: 3,
                pointBackgroundColor: "rgba(78, 115, 223, 1)",
                pointBorderColor: "rgba(78, 115, 223, 1)",
                pointHoverRadius: 3,
                pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                pointHitRadius: 10,
                pointBorderWidth: 2,
                data: communicationsData,
            }],
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Mails Pie Chart
    const mailsLabels = @json($mailsLabels);
    const mailsData = @json($mailsData);

    new Chart(document.getElementById('mailsChart'), {
        type: 'doughnut',
        data: {
            labels: mailsLabels,
            datasets: [{
                data: mailsData,
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
        }
    });

    // Slips Pie Chart
    const slipsLabels = @json($slipsLabels);
    const slipsData = @json($slipsData);

    new Chart(document.getElementById('slipsChart'), {
        type: 'doughnut',
        data: {
            labels: slipsLabels,
            datasets: [{
                data: slipsData,
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
        }
    });

    // Dollies by Type Pie Chart
    const dolliesByType = @json($dolliesByType);
    const dolliesLabels = Object.keys(dolliesByType);
    const dolliesData = Object.values(dolliesByType);

    new Chart(document.getElementById('dolliesChart'), {
        type: 'doughnut',
        data: {
            labels: dolliesLabels,
            datasets: [{
                data: dolliesData,
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
        }
    });
});
</script>
@endsection
