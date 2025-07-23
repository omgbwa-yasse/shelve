@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ __('Valeurs des paramètres') }}</h1>
        <a href="{{ route('settings.values.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>{{ __('Nouvelle valeur') }}
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ __('Liste des valeurs personnalisées') }}</h5>
        </div>
        <div class="card-body">
            @if($values->isEmpty())
                <div class="text-center py-4">
                    <i class="bi bi-sliders fs-1 text-muted"></i>
                    <p class="text-muted mt-2">{{ __('Aucune valeur personnalisée trouvée') }}</p>
                    <a href="{{ route('settings.values.create') }}" class="btn btn-primary">
                        {{ __('Créer la première valeur') }}
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('Paramètre') }}</th>
                                <th>{{ __('Utilisateur') }}</th>
                                <th>{{ __('Organisation') }}</th>
                                <th>{{ __('Valeur') }}</th>
                                <th>{{ __('Créé le') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($values as $value)
                                <tr>
                                    <td>
                                        <strong>{{ $value->setting->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $value->setting->category->name ?? 'Sans catégorie' }}</small>
                                    </td>
                                    <td>
                                        @if($value->user)
                                            <span class="badge bg-info">{{ $value->user->name }}</span>
                                        @else
                                            <span class="text-muted">{{ __('Global') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($value->organisation)
                                            <span class="badge bg-secondary">{{ $value->organisation->name }}</span>
                                        @else
                                            <span class="text-muted">{{ __('Toutes') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <code class="small">{{ json_encode($value->value) }}</code>
                                    </td>
                                    <td>{{ $value->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="{{ route('settings.values.show', $value) }}" 
                                               class="btn btn-sm btn-outline-info" title="{{ __('Voir') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('settings.values.edit', $value) }}" 
                                               class="btn btn-sm btn-outline-warning" title="{{ __('Modifier') }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('settings.values.destroy', $value) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        title="{{ __('Supprimer') }}"
                                                        onclick="return confirm('{{ __('Êtes-vous sûr de vouloir supprimer cette valeur ?') }}')">
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
