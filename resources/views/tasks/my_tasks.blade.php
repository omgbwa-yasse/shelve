@extends('layouts.app')

<style>
    .gantt-chart {
        display: flex;
        flex-direction: column;
        overflow-x: auto;
    }
    .gantt-row {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        border-bottom: 1px solid #e0e0e0;
        padding-bottom: 10px;
    }
    .gantt-label {
        width: 200px;
        padding-right: 15px;
        text-align: right;
        font-weight: bold;
    }
    .gantt-bar-container {
        flex-grow: 1;
        background-color: #f8f9fa;
        height: 40px;
        position: relative;
        border-radius: 5px;
    }
    .gantt-bar {
        position: absolute;
        height: 100%;
        background-color: #007bff;
        border-radius: 5px;
        transition: all 0.3s ease;
    }
    .gantt-bar:hover {
        filter: brightness(110%);
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tasks = @json($tasks->items());
        const ganttChart = document.getElementById('gantt-chart');
        const taskList = document.getElementById('task-list');
        const toggleViewBtn = document.getElementById('toggleView');

        function renderGanttChart() {
            ganttChart.innerHTML = '';

            if (tasks.length === 0) {
                ganttChart.innerHTML = '<p class="text-center text-muted">Aucune tâche disponible pour le diagramme de Gantt.</p>';
                return;
            }

            let minDate = new Date(tasks[0].start_date);
            let maxDate = new Date(tasks[0].start_date);
            tasks.forEach(task => {
                if (task.start_date) {
                    const startDate = new Date(task.start_date);
                    const endDate = new Date(startDate.getTime() + task.duration * 60 * 60 * 1000);
                    if (startDate < minDate) minDate = startDate;
                    if (endDate > maxDate) maxDate = endDate;
                }
            });

            const totalDuration = maxDate.getTime() - minDate.getTime();

            tasks.forEach(task => {
                if (!task.start_date) return;

                const row = document.createElement('div');
                row.className = 'gantt-row';

                const label = document.createElement('div');
                label.className = 'gantt-label';
                label.textContent = task.name;
                row.appendChild(label);

                const barContainer = document.createElement('div');
                barContainer.className = 'gantt-bar-container';

                const bar = document.createElement('div');
                bar.className = 'gantt-bar';
                const startDate = new Date(task.start_date);
                const leftOffset = ((startDate.getTime() - minDate.getTime()) / totalDuration) * 100;
                const width = (task.duration * 60 * 60 * 1000 / totalDuration) * 100;
                bar.style.left = `${leftOffset}%`;
                bar.style.width = `${width}%`;

                bar.title = `${task.name}\nDébut: ${startDate.toLocaleString()}\nDurée: ${task.duration} heures`;

                barContainer.appendChild(bar);
                row.appendChild(barContainer);

                ganttChart.appendChild(row);
            });
        }

        renderGanttChart();

        toggleViewBtn.addEventListener('click', function() {
            if (ganttChart.style.display !== 'none') {
                ganttChart.style.display = 'none';
                taskList.style.display = 'block';
                this.textContent = 'Vue Gantt';
            } else {
                ganttChart.style.display = 'block';
                taskList.style.display = 'none';
                this.textContent = 'Vue Liste';
            }
        });
    });
</script>
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0"><i class="bi bi-list-task me-2"></i>Mes Tâches</h2>
                        <button class="btn btn-light btn-sm" id="toggleView">Changer de vue</button>
                    </div>
                    <div class="card-body">
                        <div id="gantt-chart" class="mb-4">
                            <!-- Le diagramme de Gantt sera inséré ici -->
                        </div>
                        <div id="task-list" style="display: none;">
                            <table class="table table-hover">
                                <thead class="table-light">
                                <tr>
                                    <th>Nom</th>
                                    <th>Description</th>
                                    <th>Durée</th>
                                    <th>Date de début</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($tasks as $task)
                                    <tr>
                                        <td>{{ $task->name }}</td>
                                        <td>{{ Str::limit($task->description, 50) }}</td>
                                        <td>{{ $task->duration }} heures</td>
                                        <td>{{ $task->start_date ? $task->start_date : 'Non définie' }}</td>
                                        <td><span class="badge {{ $task->status ? $task->status->color() : 'bg-secondary' }}">{{ $task->status ? $task->status->label() : 'N/A' }}</span></td>
                                        <td>
                                            <a href="{{ route('workflows.tasks.show', $task) }}" class="btn btn-outline-primary btn-sm">Voir</a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center">
                            {{ $tasks->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')

@endpush

@push('scripts')

@endpush
