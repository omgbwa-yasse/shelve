<!-- resources/views/bulletin-boards/posts/show.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <!-- En-tête du post -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h1 class="h3 mb-2">{{ $post->name }}</h1>
                                <div class="text-muted">
                                    Publié par {{ $post->user->name }} • {{ $post->created_at->diffForHumans() }}
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                @can('update', $post->bulletinBoard)
                                    <a href="{{ route('bulletin-boards.posts.edit', $post) }}" class="btn btn-outline-primary">
                                        <i class="bi bi-pencil"></i> Modifier
                                    </a>
                                @endcan
                                @can('delete', $post->bulletinBoard)
                                    <form action="{{ route('bulletin-boards.posts.destroy', $post) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger"
                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette publication ?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenu du post -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                        <span class="badge bg-{{ $post->status == 'published' ? 'success' : 'warning' }}">
                            {{ ucfirst($post->status) }}
                        </span>
                        </div>
                        <div class="post-content">
                            {!! nl2br(e($post->description)) !!}
                        </div>
                    </div>
                </div>

                <!-- Pièces jointes avec vignettes -->
                @if($post->bulletinBoard->attachments->count() > 0)
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Documents joints</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                @foreach($post->bulletinBoard->attachments as $attachment)
                                    <div class="col-md-3 col-sm-6">
                                        <div class="card h-100">
                                            <div class="position-relative" style="height: 140px;">
                                                <img src="{{ $attachment->thumbnail_path
                                        ? Storage::url($attachment->thumbnail_path)
                                        : asset('icons/file.png') }}"
                                                     class="card-img-top"
                                                     alt="{{ $attachment->name }}"
                                                     style="height: 100%; object-fit: cover;">
                                            </div>
                                            <div class="card-body p-2">
                                                <p class="card-text small text-truncate" title="{{ $attachment->name }}">
                                                    {{ $attachment->name }}
                                                </p>
                                                <a href="{{ route('bulletin-boards.attachments.download', $attachment) }}"
                                                   class="btn btn-sm btn-outline-primary w-100">
                                                    <i class="bi bi-download"></i> Télécharger
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-lg-4">
                <!-- Organisations -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Organisations concernées</h5>
                    </div>
                    <div class="card-body">
                        @if($post->bulletinBoard->organisations->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($post->bulletinBoard->organisations as $organisation)
                                    <div class="list-group-item">
                                        <i class="bi bi-building me-2"></i>
                                        {{ $organisation->name }}
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0">Aucune organisation associée</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
