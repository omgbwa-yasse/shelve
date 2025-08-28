@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>{{ $template->name }}</h2>
                    <div>
                        <a href="{{ route('public.templates.edit', $template) }}" class="btn btn-warning btn-sm">Modifier</a>
                        <form action="{{ route('public.templates.destroy', $template) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce modèle ?')">Supprimer</button>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    <div class="mb-4">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Type:</strong>
                            </div>
                            <div class="col-md-8">
                                @switch($template->type)
                                    @case('page')
                                        <span class="badge bg-secondary">Page</span>
                                        @break
                                    @case('email')
                                        <span class="badge bg-primary">Email</span>
                                        @break
                                    @case('notification')
                                        <span class="badge bg-info">Notification</span>
                                        @break
                                    @default
                                        <span class="badge bg-light">{{ $template->type }}</span>
                                @endswitch
                            </div>
                        </div>

                        @if($template->description)
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong>Description:</strong>
                                </div>
                                <div class="col-md-8">
                                    {{ $template->description }}
                                </div>
                            </div>
                        @endif

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Dernière modification:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $template->updated_at->format('d/m/Y H:i') }}
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <strong>Contenu:</strong>
                                <div class="mt-2 p-3 bg-light rounded">
                                    {{ $template->content }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('public.templates.index') }}" class="btn btn-secondary">Retour à la liste</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
