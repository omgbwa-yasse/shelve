@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1 class="mb-4">Tableau de bord</h1>

        <div class="row">
            <!-- Statistiques générales -->
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Statistiques générales</h5>
                        <p class="card-text">Utilisateurs: {{ $totalUsers }}</p>
                        <p class="card-text">Organisations: {{ $totalOrganisations }}</p>
                        <p class="card-text">Conteneurs: {{ $totalContainers }}</p>
                    </div>
                </div>
            </div>

            <!-- Statistiques des communications -->
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Communications</h5>
                        <p class="card-text">Total: {{ $totalCommunications }}</p>
                        <p class="card-text">En attente: {{ $pendingCommunications }}</p>
                    </div>
                </div>
            </div>

            <!-- Statistiques des courriers -->
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Courriers</h5>
                        <p class="card-text">Total: {{ $totalMails }}</p>
                    </div>
                </div>
            </div>

            <!-- Statistiques des enregistrements -->
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Enregistrements</h5>
                        <p class="card-text">Total: {{ $totalRecords }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Graphique des communications -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Évolution des communications</h5>
                        <canvas id="communicationsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Graphique des courriers par priorité -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Courriers par priorité</h5>
                        <canvas id="mailsPriorityChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Graphique des bordereaux par statut -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Bordereaux par statut</h5>
                        <canvas id="slipsStatusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Statistiques des outils -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Outils</h5>
                        <p class="card-text">Activités: {{ $totalActivities }}</p>
                        <p class="card-text">Communicabilités: {{ $totalCommunicabilities }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Graphique des communications
        var communicationsCtx = document.getElementById('communicationsChart').getContext('2d');
        new Chart(communicationsCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($communicationsDates) !!},
                datasets: [{
                    label: 'Nombre de communications',
                    data: {!! json_encode($communicationsData) !!},
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Graphique des courriers par priorité
        var mailsPriorityCtx = document.getElementById('mailsPriorityChart').getContext('2d');
        new Chart(mailsPriorityCtx, {
            type: 'pie',
            data: {
                labels: {!! json_encode($mailsLabels) !!},
                datasets: [{
                    data: {!! json_encode($mailsData) !!},
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });

        // Graphique des bordereaux par statut
        var slipsStatusCtx = document.getElementById('slipsStatusChart').getContext('2d');
        new Chart(slipsStatusCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($slipsLabels) !!},
                datasets: [{
                    label: 'Nombre de bordereaux',
                    data: {!! json_encode($slipsData) !!},
                    backgroundColor: 'rgb(75, 192, 192)'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
@endsection

@section('scripts')

@endsection
