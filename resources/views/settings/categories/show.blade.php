@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ $category->name }}</h1>
        <div>
            <a href="{{ route('settings.categories.edit', $category) }}" class="btn btn-warning">
                <i class="bi bi-pencil me-2"></i>{{ __('Modifier') }}
            </a>
            <a href="{{ route('settings.categories.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>{{ __('Retour') }}
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Détails de la catégorie') }}</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">{{ __('Nom') }}:</dt>
                        <dd class="col-sm-9">{{ $category->name }}</dd>

                        <dt class="col-sm-3">{{ __('Description') }}:</dt>
                        <dd class="col-sm-9">{{ $category->description ?: __('Aucune description') }}</dd>

                        <dt class="col-sm-3">{{ __('Parent') }}:</dt>
                        <dd class="col-sm-9">
                            @if($category->parent)
                                <a href="{{ route('settings.categories.show', $category->parent) }}" class="badge bg-secondary text-decoration-none">
                                    {{ $category->parent->name }}
                                </a>
                            @else
                                <span class="text-muted">{{ __('Catégorie racine') }}</span>
                            @endif
                        </dd>

                        <dt class="col-sm-3">{{ __('Créé le') }}:</dt>
                        <dd class="col-sm-9">{{ $category->created_at->format('d/m/Y H:i') }}</dd>

                        <dt class="col-sm-3">{{ __('Modifié le') }}:</dt>
                        <dd class="col-sm-9">{{ $category->updated_at->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>

            @if($category->children->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('Sous-catégories') }} ({{ $category->children->count() }})</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($category->children as $child)
                                <div class="col-md-6 mb-2">
                                    <div class="border rounded p-3">
                                        <h6><a href="{{ route('settings.categories.show', $child) }}">{{ $child->name }}</a></h6>
                                        <p class="text-muted small mb-0">{{ Str::limit($child->description, 100) }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if($category->settings->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('Paramètres') }} ({{ $category->settings->count() }})</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>{{ __('Nom') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th>{{ __('Système') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($category->settings as $setting)
                                        <tr>
                                            <td><strong>{{ $setting->name }}</strong></td>
                                            <td><span class="badge bg-info">{{ $setting->type }}</span></td>
                                            <td>{{ Str::limit($setting->description, 50) }}</td>
                                            <td>
                                                @if($setting->is_system)
                                                    <span class="badge bg-warning">{{ __('Système') }}</span>
                                                @else
                                                    <span class="badge bg-success">{{ __('Utilisateur') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('settings.definitions.show', $setting) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
