@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h2 mb-0">
                <i class="bi bi-diagram-3 me-2"></i>
                {{ __('Instances de workflow') }}
            </h1>
        </div>
        <div class="col-auto">
            @can('workflow.instance.create')
            <a href="{{ route('workflow.instances.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                {{ __('Nouvelle instance') }}
            </a>
            @endcan
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('workflow.instances.index') }}" method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="{{ __('Rechercher...') }}" value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select name="template" class="form-control">
                            <option value="">{{ __('Tous les modèles') }}</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" {{ request('template') == $template->id ? 'selected' : '' }}>
                                    {{ $template->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-control">
                            <option value="">{{ __('Tous les statuts') }}</option>
                            @foreach(\App\Enums\WorkflowStatus::cases() as $status)
                                <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="assigned" class="form-control">
                            <option value="">{{ __('Toutes les assignations') }}</option>
                            <option value="me" {{ request('assigned') == 'me' ? 'selected' : '' }}>{{ __('Assignées à moi') }}</option>
                            <option value="my-team" {{ request('assigned') == 'my-team' ? 'selected' : '' }}>{{ __('Assignées à mon équipe') }}</option>
                            <option value="my-department" {{ request('assigned') == 'my-department' ? 'selected' : '' }}>{{ __('Assignées à mon département') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100">
                            <i class="bi bi-funnel me-1"></i> {{ __('Filtrer') }}
                        </button>
                    </div>
                </div>
            </form>

            @if($instances->isEmpty())
                <div class="alert alert-info">
                    {{ __('Aucune instance de workflow trouvée.') }}
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Nom / Référence') }}</th>
                                <th>{{ __('Modèle') }}</th>
                                <th>{{ __('Étape actuelle') }}</th>
                                <th>{{ __('Statut') }}</th>
                                <th>{{ __('Progression') }}</th>
                                <th>{{ __('Créé le') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($instances as $instance)
                                <tr>
                                    <td>{{ $instance->id }}</td>
                                    <td>
                                        <a href="{{ route('workflow.instances.show', $instance) }}" class="text-decoration-none">
                                            {{ $instance->name }}
                                        </a>
                                        @if($instance->reference)
                                            <div class="small text-muted">{{ $instance->reference }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('workflow.templates.show', $instance->template) }}" class="text-decoration-none">
                                            {{ $instance->template->name }}
                                        </a>
                                    </td>
                                    <td>
                                        @if($instance->currentStep)
                                            <a href="{{ route('workflow.step-instances.show', $instance->currentStep) }}" class="text-decoration-none">
                                                {{ $instance->currentStep->step->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">{{ __('Non démarré') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $instance->status->badgeClass() }}">
                                            {{ $instance->status->label() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $instance->progress }}%;" aria-valuenow="{{ $instance->progress }}" aria-valuemin="0" aria-valuemax="100">{{ $instance->progress }}%</div>
                                        </div>
                                    </td>
                                    <td>{{ $instance->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('workflow.instances.show', $instance) }}" class="btn btn-outline-secondary" title="{{ __('Voir les détails') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            @if($instance->status->value === 'draft')
                                                @can('workflow.instance.start', $instance)
                                                <button type="button" class="btn btn-outline-primary" onclick="confirmAction('{{ route('workflow.instances.start', $instance) }}', '{{ __('Êtes-vous sûr de vouloir démarrer ce workflow ?') }}')" title="{{ __('Démarrer') }}">
                                                    <i class="bi bi-play"></i>
                                                </button>
                                                @endcan
                                            @elseif($instance->status->value === 'in_progress')
                                                @can('workflow.instance.pause', $instance)
                                                <button type="button" class="btn btn-outline-warning" onclick="confirmAction('{{ route('workflow.instances.pause', $instance) }}', '{{ __('Êtes-vous sûr de vouloir mettre en pause ce workflow ?') }}')" title="{{ __('Mettre en pause') }}">
                                                    <i class="bi bi-pause"></i>
                                                </button>
                                                @endcan
                                            @elseif($instance->status->value === 'paused')
                                                @can('workflow.instance.resume', $instance)
                                                <button type="button" class="btn btn-outline-success" onclick="confirmAction('{{ route('workflow.instances.resume', $instance) }}', '{{ __('Êtes-vous sûr de vouloir reprendre ce workflow ?') }}')" title="{{ __('Reprendre') }}">
                                                    <i class="bi bi-play"></i>
                                                </button>
                                                @endcan
                                            @endif

                                            @if(!in_array($instance->status->value, ['completed', 'cancelled']))
                                                @can('workflow.instance.cancel', $instance)
                                                <button type="button" class="btn btn-outline-danger" onclick="confirmAction('{{ route('workflow.instances.cancel', $instance) }}', '{{ __('Êtes-vous sûr de vouloir annuler ce workflow ?') }}')" title="{{ __('Annuler') }}">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $instances->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    function confirmAction(url, message) {
        if (confirm(message)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            form.innerHTML = `@csrf`;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
@endpush
@endsection
