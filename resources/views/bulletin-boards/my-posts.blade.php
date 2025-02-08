<!-- resources/views/bulletin-boards/my-posts.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>Mes publications</h2>
                    <a href="{{ route('bulletin-boards.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Nouvelle publication
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistiques rapides -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Publications actives</h6>
                                <h3 class="mb-0">{{ $stats['active_posts'] }}</h3>
                            </div>
                            <div class="text-primary">
                                <i class="bi bi-file-text fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Événements à venir</h6>
                                <h3 class="mb-0">{{ $stats['upcoming_events'] }}</h3>
                            </div>
                            <div class="text-success">
                                <i class="bi bi-calendar-event fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Brouillons</h6>
                                <h3 class="mb-0">{{ $stats['drafts'] }}</h3>
                            </div>
                            <div class="text-warning">
                                <i class="bi bi-pencil-square fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Total commentaires</h6>
                                <h3 class="mb-0">{{ $stats['total_comments'] }}</h3>
                            </div>
                            <div class="text-info">
                                <i class="bi bi-chat-dots fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Onglets -->
        <div class="card">
            <div class="card-header bg-white">
                <ul class="nav nav-tabs card-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') == 'published' ? 'active' : '' }}"
                           href="?status=published">Publiées</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') == 'draft' ? 'active' : '' }}"
                           href="?status=draft">Brouillons</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('type') == 'event' ? 'active' : '' }}"
                           href="?type=event">Événements</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Type</th>
                            <th>État</th>
                            <th>Date de création</th>
                            <th>Dernière modification</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($posts as $post)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <a href="{{ route('bulletin-boards.show', $post) }}"
                                               class="text-decoration-none">
                                                {{ $post->name }}
                                            </a>
                                            <div class="small text-muted">
                                                {{ Str::limit($post->description, 50) }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                <span class="badge bg-{{ $post->type == 'event' ? 'success' : 'primary' }}">
                                    {{ ucfirst($post->type) }}
                                </span>
                                </td>
                                <td>
                                <span class="badge bg-{{ $post->status == 'published' ? 'success' : 'warning' }}">
                                    {{ ucfirst($post->status) }}
                                </span>
                                </td>
                                <td>{{ $post->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $post->updated_at->diffForHumans() }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('bulletin-boards.edit', $post) }}"
                                           class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="confirmDelete({{ $post->id }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    Aucune publication trouvée
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $posts->links() }}
        </div>
    </div>

    @push('scripts')
        <script>
            function confirmDelete(postId) {
                if (confirm('Êtes-vous sûr de vouloir supprimer cette publication ?')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/bulletin-boards/${postId}`;
                    form.innerHTML = `
            @csrf
                    @method('DELETE')
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        </script>
    @endpush
@endsection
