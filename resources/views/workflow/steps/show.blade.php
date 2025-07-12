@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h2 mb-0">
                <i class="bi bi-list-ol me-2"></i>
                {{ $step->name }}
            </h1>
            <p class="text-muted mt-2">
                {{ __('Étape du modèle') }}: <a href="{{ route('workflows.templates.show', $step->template) }}">{{ $step->template->name }}</a>
            </p>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <a href="{{ route('workflows.templates.show', $step->template) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    {{ __('Retour au modèle') }}
                </a>

                @can('update', $step->template)
                <a href="{{ route('workflows.steps.edit', [$step->template, $step]) }}" class="btn btn-outline-primary">
                    <i class="bi bi-pencil me-1"></i>
                    {{ __('Modifier') }}
                </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Colonne principale -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Détails de l\'étape') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Description') }}</div>
                        <div class="col-md-9">{{ $step->description ?: __('Aucune description') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Type d\'étape') }}</div>
                        <div class="col-md-9">{{ $step->step_type->label() }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Position') }}</div>
                        <div class="col-md-9">{{ $step->order_index + 1 }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Durée estimée') }}</div>
                        <div class="col-md-9">
                            @if($step->estimated_duration)
                                {{ $step->estimated_duration }} {{ __('jour(s)') }}
                            @else
                                {{ __('Non définie') }}
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Obligatoire') }}</div>
                        <div class="col-md-9">
                            @if($step->is_required)
                                <span class="badge bg-warning text-dark">{{ __('Oui') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('Non') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Peut être ignorée') }}</div>
                        <div class="col-md-9">
                            @if($step->can_be_skipped)
                                <span class="badge bg-info">{{ __('Oui') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('Non') }}</span>
                            @endif
                        </div>
                    </div>
                    @if($step->configuration)
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Configuration') }}</div>
                        <div class="col-md-9">
                            <pre class="bg-light p-2 rounded"><code>{{ json_encode($step->configuration, JSON_PRETTY_PRINT) }}</code></pre>
                        </div>
                    </div>
                    @endif
                    @if($step->conditions)
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Conditions') }}</div>
                        <div class="col-md-9">
                            <pre class="bg-light p-2 rounded"><code>{{ json_encode($step->conditions, JSON_PRETTY_PRINT) }}</code></pre>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Assignations -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Assignations') }}</h5>
                </div>
                <div class="card-body">
                    @forelse($step->assignments as $assignment)
                    <div class="assignment-item mb-3 p-3 border rounded">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <span class="badge bg-primary">{{ ucfirst($assignment->assignee_type) }}</span>
                            </div>
                            <div class="col-md-6">
                                @if($assignment->assignee_type == 'user' && $assignment->user)
                                    <strong>{{ $assignment->user->name }}</strong>
                                    <div class="text-muted small">{{ $assignment->user->email }}</div>
                                @elseif($assignment->assignee_type == 'organisation' && $assignment->organisation)
                                    <strong>{{ $assignment->organisation->name }}</strong>
                                    <div class="text-muted small">{{ __('Organisation') }}</div>
                                @else
                                    <span class="text-muted">{{ __('Non assigné') }}</span>
                                @endif
                            </div>
                            <div class="col-md-3">
                                @if($assignment->assignment_rules && isset($assignment->assignment_rules['role']))
                                    <span class="badge bg-secondary">{{ $assignment->assignment_rules['role'] }}</span>
                                @endif
                                @if($assignment->allow_reassignment)
                                    <span class="badge bg-info">{{ __('Réassignable') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-person-x fs-1 d-block mb-2"></i>
                        {{ __('Aucune assignation définie pour cette étape') }}
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Colonne latérale -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0">{{ __('Actions') }}</h6>
                </div>
                <div class="card-body">
                    @can('update', $step->template)
                    <a href="{{ route('workflows.steps.edit', [$step->template, $step]) }}" class="btn btn-outline-primary btn-sm w-100 mb-2">
                        <i class="bi bi-pencil me-1"></i>
                        {{ __('Modifier l\'étape') }}
                    </a>
                    @endcan

                    @can('update', $step->template)
                    <form action="{{ route('workflows.steps.destroy', [$step->template, $step]) }}" method="POST" class="d-inline w-100" onsubmit="return confirm('{{ __('Êtes-vous sûr de vouloir supprimer cette étape ?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                            <i class="bi bi-trash me-1"></i>
                            {{ __('Supprimer l\'étape') }}
                        </button>
                    </form>
                    @endcan
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0">{{ __('Informations système') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-5 fw-bold small">{{ __('Créé le') }}</div>
                        <div class="col-7 small">{{ $step->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-bold small">{{ __('Modifié le') }}</div>
                        <div class="col-7 small">{{ $step->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="row">
                        <div class="col-5 fw-bold small">{{ __('ID') }}</div>
                        <div class="col-7 small">#{{ $step->id }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
