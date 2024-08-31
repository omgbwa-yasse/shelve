@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Dashboard</h1>

        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Communications</h5>
                        <p>Total: {{ $totalCommunications }}</p>
                        <p>Pending: {{ $pendingCommunications }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Dollies</h5>
                        <p>Total: {{ $totalDollies }}</p>
                        <ul>
                            @foreach($dolliesByType as $type => $count)
                                <li>Type {{ $type }}: {{ $count }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Mails</h5>
                        <p>Total: {{ $totalMails }}</p>
                        <ul>
                            @foreach($mailsByPriority as $priority => $count)
                                <li>Priority {{ $priority }}: {{ $count }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Repository</h5>
                        <p>Total Records: {{ $totalRecords }}</p>
                        <ul>
                            @foreach($recordsByLevel as $level => $count)
                                <li>Level {{ $level }}: {{ $count }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Tools</h5>
                        <p>Total Activities: {{ $totalActivities }}</p>
                        <p>Total Communicabilities: {{ $totalCommunicabilities }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Transferring</h5>
                        <p>Total Slips: {{ $totalSlips }}</p>
                        <ul>
                            @foreach($slipsByStatus as $status => $count)
                                <li>Status {{ $status }}: {{ $count }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">General Statistics</h5>
                        <p>Total Users: {{ $totalUsers }}</p>
                        <p>Total Organisations: {{ $totalOrganisations }}</p>
                        <p>Total Containers: {{ $totalContainers }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-6">
                <canvas id="communicationsChart"></canvas>
            </div>
            <div class="col-md-6">
                <canvas id="mailsChart"></canvas>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-6">
                <canvas id="slipsChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Chart.js scripts
        document.addEventListener('DOMContentLoaded', function() {
            const communicationsCtx = document.getElementById('communicationsChart').getContext('2d');
            const mailsCtx = document.getElementById('mailsChart').getContext('2d');
            const slipsCtx = document.getElementById('slipsChart').getContext('2d');

            const communicationsChart = new Chart(communicationsCtx, {
                type: 'line',
                data: {
                    labels: @json($communicationsDates),
                    datasets: [{
                        label: 'Communications',
                        data: @json($communicationsData),
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            const mailsChart = new Chart(mailsCtx, {
                type: 'pie',
                data: {
                    labels: @json($mailsLabels),
                    datasets: [{
                        label: 'Mails',
                        data: @json($mailsData),
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(255, 159, 64, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Mails by Priority'
                        }
                    }
                }
            });

            const slipsChart = new Chart(slipsCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($slipsLabels),
                    datasets: [{
                        label: 'Slips',
                        data: @json($slipsData),
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(255, 159, 64, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Slips by Status'
                        }
                    }
                }
            });
        });
    </script>
@endsection
