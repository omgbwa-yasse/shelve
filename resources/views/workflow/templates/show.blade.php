@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h2 mb-0"><i class="bi bi-file-earmark-text me-2"></i>{{ $template->name }}</h1>
            <div class="text-muted">{{ __('Modèle de workflow') }} #{{ $template->id }}</div>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                @can('update', $template)
                    <a href="{{ route('workflows.templates.edit', $template) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>{{ __('Modifier') }}</a>
                @endcan
                <a href="{{ route('workflows.templates.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>{{ __('Retour à la liste') }}</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light"><h5 class="card-title mb-0">{{ __('Informations') }}</h5></div>
                <div class="card-body">
                    <div class="mb-3"><label class="text-muted small">{{ __('Statut') }}</label><div>@if($template->is_active)<span class="badge bg-success">{{ __('Actif') }}</span>@else<span class="badge bg-danger">{{ __('Inactif') }}</span>@endif</div></div>
                    <div class="mb-3"><label class="text-muted small">{{ __('Description') }}</label><div>{{ $template->description ?: __('Aucune description') }}</div></div>
                    <div class="mb-3"><label class="text-muted small">{{ __('Créé par') }}</label><div>{{ $template->creator->name ?? 'N/A' }}</div></div>
                    <div class="mb-3"><label class="text-muted small">{{ __('Date de création') }}</label><div>{{ $template->created_at->format('d/m/Y H:i') }}</div></div>
                    <div class="mb-3"><label class="text-muted small">{{ __('Dernière modification') }}</label><div>{{ $template->updated_at->format('d/m/Y H:i') }}</div></div>
                </div>
                <div class="card-footer bg-light d-flex justify-content-between">
                    @can('toggleActive', $template)
                    <form action="{{ route('workflows.templates.toggle-active', $template) }}" method="POST">@csrf<button type="submit" class="btn btn-sm btn-{{ $template->is_active ? 'warning' : 'success' }}"><i class="bi bi-{{ $template->is_active ? 'toggle-off' : 'toggle-on' }} me-1"></i>{{ $template->is_active ? __('Désactiver') : __('Activer') }}</button></form>
                    @endcan
                    <div>
                        @can('duplicate', $template)
                        <form action="{{ route('workflows.templates.duplicate', $template) }}" method="POST" class="d-inline">@csrf<button type="submit" class="btn btn-sm btn-info"><i class="bi bi-copy me-1"></i>{{ __('Dupliquer') }}</button></form>
                        @endcan
                        @can('delete', $template)
                        <form action="{{ route('workflows.templates.destroy', $template) }}" method="POST" class="d-inline delete-form">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash me-1"></i>{{ __('Supprimer') }}</button></form>
                        @endcan
                    </div>
                </div>
            </div>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light"><h5 class="card-title mb-0">{{ __('Statistiques') }}</h5></div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3"><div class="h4">{{ $template->steps->count() }}</div><div class="text-muted small">{{ __('Étapes') }}</div></div>
                        <div class="col-6 mb-3"><div class="h4">{{ $template->instances->count() }}</div><div class="text-muted small">{{ __('Instances') }}</div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <ul class="nav nav-tabs" id="templateTabs" role="tablist">
                <li class="nav-item" role="presentation"><button class="nav-link active" id="steps-tab" data-bs-toggle="tab" data-bs-target="#steps" type="button" role="tab"><i class="bi bi-list-task me-1"></i>{{ __('Étapes du workflow') }}</button></li>
            </ul>
            <div class="tab-content" id="templateTabsContent">
                <!-- Onglet Étapes -->
                <div class="tab-pane fade show active" id="steps" role="tabpanel">
                    <div class="card shadow-sm border-top-0">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">{{ __('Étapes du workflow') }}</h5>
                            @can('create', [\App\Models\WorkflowStep::class, $template])
                                <a href="{{ route('workflows.templates.steps.create', $template) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    {{ __('Ajouter une étape') }}
                                </a>
                            @endcan
                        </div>
                        <div class="card-body">
                            @if($template->steps->isEmpty())
                                <div class="alert alert-info">{{ __('Aucune étape définie pour ce modèle de workflow.') }}</div>
                            @else
                                <div class="workflow-steps">
                                    @foreach($template->steps->sortBy('order_index') as $step)
                                        <div class="workflow-step-card mb-3 p-3 border rounded-3 {{ $step->type ? 'bg-light' : '' }}">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h5 class="mb-1">
                                                        <span class="badge bg-secondary me-2">{{ $step->order_index + 1 }}</span>
                                                        {{ $step->name }}
                                                    </h5>
                                                    <div class="small text-muted mb-2">{{ $step->description }}</div>
                                                    <div class="mb-2">
                                                        <span class="badge bg-info">{{ $step->type ? $step->type->label() : 'Standard' }}</span>
                                                        @if($step->is_required)
                                                            <span class="badge bg-danger">{{ __('Obligatoire') }}</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ __('Optionnelle') }}</span>
                                                        @endif
                                                    </div>
                                                    @if($step->assignments->isNotEmpty())
                                                        <div class="mt-2">
                                                            <strong class="small">{{ __('Assigné à:') }}</strong>
                                                            <div class="mt-1">
                                                                @foreach($step->assignments as $assignment)
                                                                    <span class="badge bg-light text-dark me-1 mb-1 py-1 px-2">
                                                                        @if($assignment->assignee_type == 'user')
                                                                            <i class="bi bi-person me-1"></i>
                                                                            {{ $assignment->assigneeUser->name ?? 'N/A' }}
                                                                        @elseif($assignment->assignee_type == 'organisation')
                                                                            <i class="bi bi-building me-1"></i>
                                                                            {{ $assignment->assigneeOrganisation->name ?? 'N/A' }}
                                                                        @else
                                                                            <i class="bi bi-question-circle me-1"></i>
                                                                            {{ __('Inconnu') }}
                                                                        @endif
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('workflows.steps.edit', $step) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                                    <form action="{{ route('workflows.steps.destroy', $step) }}" method="POST" class="delete-step-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @can('reorder', [$template])
                                    <div class="text-center mt-4">
                                        <button id="reorderStepsBtn" class="btn btn-outline-primary">
                                            <i class="bi bi-arrow-down-up me-1"></i>{{ __('Réorganiser les étapes') }}
                                        </button>
                                    </div>
                                @endcan
                            @endif
                        </div>
                    </div>
                </div>
            </div><!-- /tab-content -->
        </div><!-- /col-lg-8 -->
    </div><!-- /row -->
</div><!-- /container-fluid -->
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion de la suppression des étapes
    document.querySelectorAll('.delete-step-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (confirm('{{ __("Êtes-vous sûr de vouloir supprimer cette étape ?") }}')) {
                this.submit();
            }
        });
    });

    // Gestion de la réorganisation des étapes
    const reorderBtn = document.getElementById('reorderStepsBtn');
    if (reorderBtn) {
        reorderBtn.addEventListener('click', function() {
            // Logique de réorganisation des étapes
            alert('{{ __("Fonctionnalité de réorganisation à implémenter") }}');
        });
    }
});
</script>
@endsection
