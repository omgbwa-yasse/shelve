@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h1 class="h2 mb-0">
                    <i class="bi bi-diagram-3 me-2"></i>Organigramme
                </h1>
            </div>
            <div class="col-auto">
                <!-- Boutons d'export -->
                <div class="btn-group me-2">
                    <a href="{{ route('organisations.export.excel') }}" class="btn btn-success">
                        <i class="bi bi-file-excel me-1"></i>Excel
                    </a>
                    <a href="{{ route('organisations.export.pdf') }}" class="btn btn-danger">
                        <i class="bi bi-file-pdf me-1"></i>PDF
                    </a>
                </div>

                <!-- Boutons de vue -->
                <div class="btn-group me-2" role="group" aria-label="Vue">
                    <input type="radio" class="btn-check" name="viewType" id="tableView" checked>
                    <label class="btn btn-outline-secondary" for="tableView">
                        <i class="bi bi-table me-1"></i>Table
                    </label>

                    <input type="radio" class="btn-check" name="viewType" id="chartView">
                    <label class="btn btn-outline-secondary" for="chartView">
                        <i class="bi bi-diagram-2 me-1"></i>Organigramme
                    </label>
                </div>

                <a href="{{ route('organisations.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Ajouter une unité
                </a>
            </div>
        </div>

        <!-- Vue Tableau -->
        <div id="tableViewContent" class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                        <tr>
                            <th><i class="bi bi-hash me-1"></i>Code</th>
                            <th><i class="bi bi-building me-1"></i>Nom</th>
                            <th><i class="bi bi-text-left me-1"></i>Description</th>
                            <th><i class="bi bi-diagram-2 me-1"></i>Parent</th>
                            <th class="text-end">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($organisations as $organisation)
                            <tr>
                                <td>{{ $organisation->code }}</td>
                                <td>{{ $organisation->name }}</td>
                                <td>{{ $organisation->description }}</td>
                                <td>
                                    @if($organisation->parent)
                                        <span class="badge bg-secondary">{{ $organisation->parent->code }}</span>
                                        {{ $organisation->parent->name }}
                                    @else
                                        <span class="badge bg-primary">Direction</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('organisations.show', $organisation->id) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>Voir
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Vue Organigramme -->
        <div id="chartViewContent" class="card shadow-sm" style="display: none;">
            <div class="card-body">
                <div id="mermaidChart" class="d-flex justify-content-center" style="min-height: 700px;"></div>
            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/mermaid/dist/mermaid.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
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

                function buildMermaidDiagram() {
                    const organisations = @json($organisations);
                    let mermaidCode = 'graph TD\n';

                    mermaidCode += 'classDef root fill:#0d6efd,stroke:#0d6efd,color:white,stroke-width:2px\n';
                    mermaidCode += 'classDef unit fill:#198754,stroke:#198754,color:white,stroke-width:2px\n';

                    organisations.forEach(org => {
                        const nodeId = `O${org.id}`;
                        let label = `${org.code}<br/>${org.name}`;
                        if (org.description) {
                            label += `<br/><small><i>${org.description}</i></small>`;
                        }
                        mermaidCode += `${nodeId}["${label}"]\n`;

                        if (!org.parent_id) {
                            mermaidCode += `class ${nodeId} root\n`;
                        } else {
                            mermaidCode += `class ${nodeId} unit\n`;
                            mermaidCode += `O${org.parent_id} --> ${nodeId}\n`;
                        }
                    });

                    return mermaidCode;
                }

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

                            const mermaidDiv = document.getElementById('mermaidChart');
                            mermaidDiv.innerHTML = `<pre class="mermaid">${buildMermaidDiagram()}</pre>`;
                            mermaid.init(undefined, '.mermaid');
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
