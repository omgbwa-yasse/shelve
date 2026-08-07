@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>{{ $page->title }}</h2>
                    <div>
                        <a href="{{ route('public.pages.edit', $page) }}" class="btn btn-warning btn-sm">Modifier</a>
                        <form action="{{ route('public.pages.destroy', $page) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette page ?')">Supprimer</button>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    <div class="mb-4">
                        <div class="text-muted mb-3">
                            Dernière modification le {{ $page->updated_at->format('d/m/Y à H:i') }}
                            @if($page->author)
                                par {{ $page->author->name }}
                            @endif
                        </div>
                        <div class="page-content">
                            {{ $page->content }}
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('public.pages.index') }}" class="btn btn-secondary">Retour à la liste</a>
                        @if(!$page->is_published)
                            <form action="{{ route('public.pages.update', $page) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="is_published" value="1">
                                <button type="submit" class="btn btn-success">Publier maintenant</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
