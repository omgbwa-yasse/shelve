@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h2 mb-0">
                <i class="bi bi-list-ol me-2"></i>
                {{ __('Étapes du modèle de workflow') }}: {{ $template->name }}
            </h1>
            <p class="text-muted mt-2">
                {{ $template->description }}
            </p>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <a href="{{ route('workflows.templates.show', $template) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    {{ __('Retour au modèle') }}
                </a>
                @can('workflow.step.create')
                <a href="{{ route('workflows.templates.steps.create', $template) }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>
                    {{ __('Ajouter une étape') }}
                </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if($steps->isEmpty())
                <div class="alert alert-info">
                    {{ __('Aucune étape définie pour ce modèle de workflow.') }}
                </div>
            @else
                <div class="mb-3">
                    @can('workflow.template.update')
                    <button id="reorderBtn" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrows-move me-1"></i>
                        {{ __('Réorganiser les étapes') }}
                    </button>
                    @endcan
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width: 5%;">{{ __('Ordre') }}</th>
                                <th style="width: 30%;">{{ __('Nom') }}</th>
                                <th style="width: 15%;">{{ __('Type') }}</th>
                                <th style="width: 15%;">{{ __('Délai (jours)') }}</th>
                                <th style="width: 15%;">{{ __('Assignés') }}</th>
                                <th style="width: 20%;" class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="stepsTableBody">
                            @foreach($steps as $step)
                                <tr data-id="{{ $step->id }}">
                                    <td>
                                        <span class="badge bg-secondary">{{ $step->order }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('workflows.steps.show', $step) }}">
                                            {{ $step->name }}
                                        </a>
                                        <div class="small text-muted">{{ Str::limit($step->description, 50) }}</div>
                                    </td>
                                    <td>{{ $step->type->label() }}</td>
                                    <td>{{ $step->deadline_days ?? '-' }}</td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @forelse($step->assignments as $assignment)
                                                <span class="badge bg-info" title="{{ $assignment->assignee_type }} {{ $assignment->assignee_id }}">
                                                    {{ Str::limit($assignment->assignee_type === 'App\\Models\\User' ? $assignment->assignee->name : $assignment->assignee->name ?? $assignment->assignee_id, 20) }}
                                                </span>
                                            @empty
                                                <span class="badge bg-light text-dark">{{ __('Non assignée') }}</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            @can('workflow.step.view', $step)
                                            <a href="{{ route('workflows.steps.show', $step) }}" class="btn btn-outline-secondary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @endcan

                                            @can('workflow.step.update', $step)
                                            <a href="{{ route('workflows.steps.edit', $step) }}" class="btn btn-outline-secondary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @endcan

                                            @can('workflow.step.delete', $step)
                                            <button type="button" class="btn btn-outline-danger" onclick="confirmDelete('{{ route('workflows.steps.destroy', $step) }}', '{{ __('Êtes-vous sûr de vouloir supprimer cette étape ?') }}')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

@can('workflow.template.update')
<!-- Modal pour la réorganisation des étapes -->
<div class="modal fade" id="reorderModal" tabindex="-1" aria-labelledby="reorderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reorderModalLabel">{{ __('Réorganiser les étapes') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Glissez et déposez les étapes pour les réorganiser.') }}</p>
                <ul class="list-group" id="sortableSteps">
                    @foreach($steps as $step)
                        <li class="list-group-item" data-id="{{ $step->id }}">
                            <i class="bi bi-grip-vertical me-2"></i>
                            <span>{{ $step->name }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                <button type="button" class="btn btn-primary" id="saveReorderBtn">{{ __('Enregistrer') }}</button>
            </div>
        </div>
    </div>
</div>
@endcan

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @can('workflow.template.update')
        // Réorganisation des étapes
        const reorderBtn = document.getElementById('reorderBtn');
        const saveReorderBtn = document.getElementById('saveReorderBtn');
        const reorderModal = new bootstrap.Modal(document.getElementById('reorderModal'));
        const sortableList = document.getElementById('sortableSteps');

        if (reorderBtn) {
            reorderBtn.addEventListener('click', function() {
                reorderModal.show();
            });
        }

        if (sortableList) {
            new Sortable(sortableList, {
                animation: 150,
                ghostClass: 'bg-light'
            });
        }

        if (saveReorderBtn) {
            saveReorderBtn.addEventListener('click', function() {
                const steps = Array.from(sortableList.children).map((item, index) => {
                    return {
                        id: item.dataset.id,
                        order: index + 1
                    };
                });

                fetch('{{ route("workflow.templates.steps.reorder", $template) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ steps })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        reorderModal.hide();
                        window.location.reload();
                    } else {
                        alert('{{ __("Une erreur est survenue lors de la réorganisation des étapes.") }}');
                    }
                });
            });
        }
        @endcan

        // Confirmation de suppression
        window.confirmDelete = function(url, message) {
            if (confirm(message)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                form.innerHTML = `
                    @csrf
                    @method('DELETE')
                `;
                document.body.appendChild(form);
                form.submit();
            }
        };
    });
</script>
@endpush
@endsection
