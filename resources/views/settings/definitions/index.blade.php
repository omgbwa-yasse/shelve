@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ __('Paramètres') }}</h1>
        <a href="{{ route('settings.definitions.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>{{ __('Nouveau paramètre') }}
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ __('Liste des paramètres') }}</h5>
        </div>
        <div class="card-body">
            @if($settings->isEmpty())
                <div class="text-center py-4">
                    <i class="bi bi-gear-wide fs-1 text-muted"></i>
                    <p class="text-muted mt-2">{{ __('Aucun paramètre trouvé') }}</p>
                    <a href="{{ route('settings.definitions.create') }}" class="btn btn-primary">
                        {{ __('Créer le premier paramètre') }}
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('Nom') }}</th>
                                <th>{{ __('Catégorie') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Valeur par défaut') }}</th>
                                <th>{{ __('Valeur actuelle') }}</th>
                                <th>{{ __('Système') }}</th>
                                <th>{{ __('Créé le') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($settings as $setting)
                                <tr>
                                    <td><strong>{{ $setting->name }}</strong></td>
                                    <td>
                                        @if($setting->category)
                                            <a href="{{ route('settings.categories.show', $setting->category) }}" class="badge bg-secondary text-decoration-none">
                                                {{ $setting->category->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">{{ __('Sans catégorie') }}</span>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-info">{{ $setting->type }}</span></td>
                                    <td>{{ Str::limit($setting->description, 50) }}</td>
                                    <td>
                                        <code class="small">{{ json_encode($setting->default_value) }}</code>
                                    </td>
                                    <td>
                                        @if($setting->hasCustomValue())
                                            <code class="small text-success">{{ json_encode($setting->value) }}</code>
                                            <small class="text-muted d-block">{{ __('Personnalisée') }}</small>
                                        @else
                                            <code class="small text-muted">{{ json_encode($setting->default_value) }}</code>
                                            <small class="text-muted d-block">{{ __('Par défaut') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($setting->is_system)
                                            <span class="badge bg-warning">{{ __('Système') }}</span>
                                        @else
                                            <span class="badge bg-success">{{ __('Utilisateur') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $setting->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="{{ route('settings.definitions.show', $setting) }}"
                                               class="btn btn-sm btn-outline-info" title="{{ __('Voir') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('settings.definitions.edit', $setting) }}"
                                               class="btn btn-sm btn-outline-warning" title="{{ __('Modifier') }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('settings.definitions.destroy', $setting) }}"
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        title="{{ __('Supprimer') }}"
                                                        onclick="return confirm('{{ __('Êtes-vous sûr de vouloir supprimer ce paramètre ?') }}')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
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
@endsection
