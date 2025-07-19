@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Gestion des schémas de thésaurus</h3>
                    <div class="card-tools">
                        <a href="{{ route('thesaurus.schemes.create') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus-circle"></i> Nouveau schéma
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Identifiant</th>
                                    <th>Titre</th>
                                    <th>Langue</th>
                                    <th>Concepts</th>
                                    <th>Namespace</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($schemes as $scheme)
                                    <tr>
                                        <td>{{ $scheme->id }}</td>
                                        <td><span class="badge badge-info">{{ $scheme->identifier }}</span></td>
                                        <td>{{ $scheme->title }}</td>
                                        <td>{{ $scheme->language }}</td>
                                        <td>
                                            <span class="badge badge-secondary">{{ $scheme->concepts->count() }}</span>
                                        </td>
                                        <td>
                                            @if ($scheme->namespace)
                                                <span class="badge badge-primary">{{ $scheme->namespace->prefix }}</span>
                                            @else
                                                <span class="badge badge-light">Aucun</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('thesaurus.schemes.show', $scheme->id) }}"
                                                   class="btn btn-sm btn-info" title="Détails">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('thesaurus.schemes.edit', $scheme->id) }}"
                                                   class="btn btn-sm btn-primary" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger"
                                                        onclick="confirmDelete({{ $scheme->id }})" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <p class="text-muted">Aucun schéma de thésaurus trouvé</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $schemes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmation de suppression</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer ce schéma de thésaurus ? Cette action est irréversible et supprimera également tous les concepts associés.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <form id="deleteForm" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete(schemeId) {
        const form = document.getElementById('deleteForm');
        form.action = `{{ route('thesaurus.schemes.destroy', '') }}/${schemeId}`;
        $('#deleteModal').modal('show');
    }
</script>
@endsection
