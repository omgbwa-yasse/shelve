@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Actualités</h2>
                    <a href="{{ route('public.news.create') }}" class="btn btn-primary">Nouvelle actualité</a>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row">
                        @foreach($news as $article)
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    @if($article->image_path)
                                        <img src="{{ asset('storage/' . $article->image_path) }}" class="card-img-top" alt="{{ $article->title }}">
                                    @endif
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $article->title }}</h5>
                                        <p class="card-text">{{ Str::limit($article->content, 150) }}</p>                        <div class="mb-2">
                            <small class="text-muted">
                                @if($article->published_at)
                                    Publié le {{ $article->published_at->format('d/m/Y') }}
                                @else
                                    Publié le {{ $article->created_at->format('d/m/Y') }}
                                @endif
                                @if($article->author)
                                    par {{ $article->author->name }}
                                @endif
                            </small>
                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <div class="d-flex justify-content-between">
                                            <a href="{{ route('public.news.show', $article) }}" class="btn btn-info btn-sm">Lire</a>
                                            <a href="{{ route('public.news.edit', $article) }}" class="btn btn-warning btn-sm">Modifier</a>
                                            <form action="{{ route('public.news.destroy', $article) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette actualité ?')">Supprimer</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
