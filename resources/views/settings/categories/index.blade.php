@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ __('Catégories de paramètres') }}</h1>
        <a href="{{ route('settings.categories.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>{{ __('Nouvelle catégorie') }}
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ __('Liste des catégories') }}</h5>
        </div>
        <div class="card-body">
            @if($categories->isEmpty())
                <div class="text-center py-4">
                    <i class="bi bi-folder-x fs-1 text-muted"></i>
                    <p class="text-muted mt-2">{{ __('Aucune catégorie trouvée') }}</p>
                    <a href="{{ route('settings.categories.create') }}" class="btn btn-primary">
                        {{ __('Créer la première catégorie') }}
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('Nom') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Parent') }}</th>
                                <th>{{ __('Paramètres') }}</th>
                                <th>{{ __('Créé le') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                                <tr>
                                    <td>
                                        <strong>{{ $category->name }}</strong>
                                        @if($category->children->count() > 0)
                                            <span class="badge bg-info ms-2">{{ $category->children->count() }} sous-catégories</span>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($category->description, 50) }}</td>
                                    <td>
                                        @if($category->parent)
                                            <span class="badge bg-secondary">{{ $category->parent->name }}</span>
                                        @else
                                            <span class="text-muted">{{ __('Racine') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $category->settings->count() }}</span>
                                    </td>
                                    <td>{{ $category->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('settings.categories.show', $category) }}"
                                               class="btn btn-sm btn-outline-info" title="{{ __('Voir') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('settings.categories.edit', $category) }}"
                                               class="btn btn-sm btn-outline-warning" title="{{ __('Modifier') }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('settings.categories.destroy', $category) }}"
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        title="{{ __('Supprimer') }}"
                                                        onclick="return confirm('{{ __('Êtes-vous sûr de vouloir supprimer cette catégorie ?') }}')">
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
