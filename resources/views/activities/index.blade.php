@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h1 class="h2 mb-0">
                    <i class="bi bi-diagram-3 me-2"></i>{{ __('Classification Plan') }}
                </h1>
            </div>
            <div class="col-auto">
                <div class="btn-group me-3" role="group" aria-label="Vue">
                    <input type="radio" class="btn-check" name="viewType" id="tableView" checked>
                    <label class="btn btn-outline-secondary" for="tableView">
                        <i class="bi bi-table me-1"></i>{{ __('Table') }}
                    </label>

                    <input type="radio" class="btn-check" name="viewType" id="chartView">
                    <label class="btn btn-outline-secondary" for="chartView">
                        <i class="bi bi-diagram-2 me-1"></i>{{ __('Chart') }}
                    </label>
                </div>

                <a href="{{ route('activities.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>{{ __('Add Activity') }}
                </a>
                <div class="btn-group me-2">
                    <a href="{{ route('activities.export.excel') }}" class="btn btn-success">
                        <i class="bi bi-file-excel me-1"></i>Excel
                    </a>
                    <a href="{{ route('activities.export.pdf') }}" class="btn btn-danger">
                        <i class="bi bi-file-pdf me-1"></i>PDF
                    </a>
                </div>
            </div>
        </div>

        <!-- Vue Tableau -->
        <div id="tableViewContent" class="card shadow-sm">
            <div id="tableViewContent" class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                            <tr>
                                <th scope="col"><i class="bi bi-hash me-1"></i>{{ __('Code') }}</th>
                                <th scope="col"><i class="bi bi-text-left me-1"></i>{{ __('Name') }}</th>
                                <th scope="col"><i class="bi bi-chat me-1"></i>{{ __('Observation') }}</th>
                                <th scope="col"><i class="bi bi-diagram-2 me-1"></i>{{ __('Parent') }}</th>
                                <th scope="col" class="text-end">{{ __('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($activities as $activity)
                                <tr>
                                    <td>{{ $activity->code }}</td>
                                    <td>{{ $activity->name }}</td>
                                    <td>{{ $activity->observation }}</td>
                                    <td>
                                        @if($activity->parent)
                                            <span class="badge bg-secondary">{{ $activity->parent->code }}</span>
                                            {{ $activity->parent->name }}
                                        @else
                                            <span class="badge bg-primary">{{ __('Mission') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('activities.show', $activity->id) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-gear me-1"></i>{{ __('Settings') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <p class="text-muted mb-0">{{ __('No activities found.') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <!-- Vue Organigramme -->
        <div id="chartViewContent" class="card shadow-sm" style="display: none;">
            <div class="card-body p-0"> <!-- Padding retiré pour maximiser l'espace -->
                <div class="d-flex justify-content-end p-3">
                    <div class="legend d-flex align-items-center gap-4">
                        <div class="d-flex align-items-center">
                            <span class="badge bg-primary me-2">■</span> {{ __('Mission') }}
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-success me-2">■</span> {{ __('Activity') }}
                        </div>
                    </div>
                </div>
                <div id="mermaidChart" class="d-flex justify-content-center" style="min-height: 700px;"></div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .mermaid {
            max-width: 100%;
            margin: 0 auto;
            font-size: 14px !important;
        }
        .mermaid .node rect {
            stroke-width: 2px !important;
        }
        .mermaid .node text {
            font-size: 14px !important;
        }
        .mermaid .edgePath .path {
            stroke-width: 2px !important;
        }
        /* Augmente la taille du diagramme */
        #mermaidChart {
            transform: scale(1.2);
            transform-origin: center center;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/mermaid/dist/mermaid.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configuration Mermaid avec thème personnalisé
            mermaid.initialize({
                startOnLoad: true,
                theme: 'default',
                flowchart: {
                    useMaxWidth: false,
                    htmlLabels: true,
                    curve: 'basis',
                    nodeSpacing: 100,
                    rankSpacing: 100,
                }
            });

            // Construire le diagramme Mermaid
            function buildMermaidDiagram() {
                const activities = @json($activities);
                let mermaidCode = 'graph TD\n';

                // Style des nœuds
                mermaidCode += 'classDef mission fill:#0d6efd,stroke:#0d6efd,color:white,stroke-width:2px\n';
                mermaidCode += 'classDef activity fill:#198754,stroke:#198754,color:white,stroke-width:2px\n';

                // Ajouter tous les nœuds avec style amélioré
                activities.forEach(activity => {
                    const nodeId = `A${activity.id}`;
                    let label = `${activity.code}<br/>${activity.name}`;
                    if (activity.observation) {
                        label += `<br/><small><i>${activity.observation}</i></small>`;
                    }
                    mermaidCode += `${nodeId}["${label}"]\n`;

                    // Appliquer la classe appropriée
                    if (!activity.parent_id) {
                        mermaidCode += `class ${nodeId} mission\n`;
                    } else {
                        mermaidCode += `class ${nodeId} activity\n`;
                    }
                });

                // Ajouter les connexions avec style
                activities.forEach(activity => {
                    if (activity.parent_id) {
                        mermaidCode += `A${activity.parent_id} --> A${activity.id}\n`;
                    }
                });

                return mermaidCode;
            }

            // Gestionnaire de basculement de vue
            document.querySelectorAll('input[name="viewType"]').forEach(input => {
                input.addEventListener('change', function() {
                    const tableView = document.getElementById('tableViewContent');
                    const chartView = document.getElementById('chartViewContent');

                    if (this.id === 'tableView') {
                        tableView.style.display = 'block';
                        chartView.style.display = 'none';
                    } else {
                        tableView.style.display = 'none';
                        chartView.style.display = 'block';

                        // Réinitialise et redessine le diagramme à chaque affichage
                        const mermaidDiv = document.getElementById('mermaidChart');
                        mermaidDiv.innerHTML = `<pre class="mermaid">${buildMermaidDiagram()}</pre>`;
                        mermaid.init(undefined, '.mermaid');
                    }
                });
            });
        });
    </script>
@endpush
