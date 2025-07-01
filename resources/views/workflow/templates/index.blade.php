

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h2 mb-0">
                <i class="bi bi-file-earmark-text me-2"></i>
                {{ __('Modèles de workflow') }}
            </h1>
        </div>
        <div class="col-auto">
            @can('workflow.template.create')
            <a href="{{ route('workflow.templates.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                {{ __('Créer un modèle') }}
            </a>
            @endcan
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('workflow.templates.index') }}" method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="{{ __('Rechercher par nom ou description') }}" value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="category" class="form-control">
                            <option value="">{{ __('Toutes les catégories') }}</option>
                            @foreach($templates->pluck('category')->unique() as $category)
                                <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="active" class="form-control">
                            <option value="">{{ __('Tous les statuts') }}</option>
                            <option value="1" {{ request('active') == '1' ? 'selected' : '' }}>{{ __('Actif') }}</option>
                            <option value="0" {{ request('active') == '0' ? 'selected' : '' }}>{{ __('Inactif') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100">
                            <i class="bi bi-funnel me-1"></i> {{ __('Filtrer') }}
                        </button>
                    </div>
                </div>
            </form>

            @if($templates->isEmpty())
                <div class="alert alert-info">
                    {{ __('Aucun modèle de workflow trouvé.') }}
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('Nom') }}</th>
                                <th>{{ __('Catégorie') }}</th>
                                <th class="text-center">{{ __('Étapes') }}</th>
                                <th class="text-center">{{ __('Instances') }}</th>
                                <th>{{ __('Statut') }}</th>
                                <th>{{ __('Créé par') }}</th>
                                <th>{{ __('Date de création') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($templates as $template)
                                <tr>
                                    <td>
                                        <a href="{{ route('workflow.templates.show', $template) }}">
                                            {{ $template->name }}
                                        </a>
                                        <div class="small text-muted">{{ Str::limit($template->description, 50) }}</div>
                                    </td>
                                    <td>{{ $template->category }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-pill badge-primary">{{ $template->steps_count }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-pill badge-secondary">{{ $template->instances_count }}</span>
                                    </td>
                                    <td>
                                        @if($template->is_active)
                                            <span class="badge badge-success">{{ __('Actif') }}</span>
                                        @else
                                            <span class="badge badge-danger">{{ __('Inactif') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $template->creator->name ?? 'N/A' }}</td>
                                    <td>{{ $template->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('workflow.templates.show', $template) }}" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="{{ __('Voir') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            @can('update', $template)
                                            <a href="{{ route('workflow.templates.edit', $template) }}" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="{{ __('Modifier') }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @endcan

                                            @can('toggleActive', $template)
                                            <form action="{{ route('workflow.templates.toggle-active', $template) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-{{ $template->is_active ? 'warning' : 'success' }}" data-bs-toggle="tooltip"
                                                        title="{{ $template->is_active ? __('Désactiver') : __('Activer') }}">
                                                    <i class="bi bi-{{ $template->is_active ? 'toggle-off' : 'toggle-on' }}"></i>
                                                </button>
                                            </form>
                                            @endcan

                                            @can('duplicate', $template)
                                            <form action="{{ route('workflow.templates.duplicate', $template) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-info" data-bs-toggle="tooltip" title="{{ __('Dupliquer') }}">
                                                    <i class="bi bi-copy"></i>
                                                </button>
                                            </form>
                                            @endcan

                                            @can('delete', $template)
                                            <form action="{{ route('workflow.templates.destroy', $template) }}" method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" data-bs-toggle="tooltip" title="{{ __('Supprimer') }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $templates->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser les tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Confirmation pour la suppression
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function(event) {
                event.preventDefault();

                if (confirm('{{ __("Êtes-vous sûr de vouloir supprimer ce modèle de workflow ?") }}')) {
                    this.submit();
                }
            });
        });
    });
</script>
@endsection
