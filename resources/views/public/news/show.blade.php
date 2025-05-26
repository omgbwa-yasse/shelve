@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>{{ $news->title }}</h2>
                    <div>
                        <a href="{{ route('public.news.edit', $news) }}" class="btn btn-warning btn-sm">Modifier</a>
                        <form action="{{ route('public.news.destroy', $news) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette actualité ?')">Supprimer</button>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    @if($news->image)
                        <div class="text-center mb-4">
                            <img src="{{ asset('storage/' . $news->image) }}" class="img-fluid rounded" alt="{{ $news->title }}">
                        </div>
                    @endif

                    <div class="mb-4">
                        <div class="text-muted mb-3">
                            Publié le {{ $news->published_at->format('d/m/Y à H:i') }}
                            @if($news->author)
                                par {{ $news->author->name }}
                            @endif
                        </div>
                        <div class="news-content">
                            {!! $news->content !!}
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('public.news.index') }}" class="btn btn-secondary">Retour à la liste</a>
                        @if(!$news->is_published)
                            <form action="{{ route('public.news.update', $news) }}" method="POST" class="d-inline">
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
