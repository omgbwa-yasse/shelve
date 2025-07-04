@extends('layouts.app')

@section('title', 'Thésaurus')

@section('content')
<div class="container-fluid">
    <h1>Gestion du thésaurus</h1>

    <div class="mb-3">
        <a href="{{ route('terms.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Ajouter un terme
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <h5>Termes du thésaurus</h5>
                </div>
                <div class="col-md-6">
                    <input type="text" id="termSearch" class="form-control" placeholder="Rechercher un terme...">
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Libellé préféré</th>
                            <th>Langue</th>
                            <th>Catégorie</th>
                            <th>Statut</th>
                            <th>Notation</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($terms as $term)
                            <tr>
                                <td>{{ $term->preferred_label }}</td>
                                <td>{{ $term->language }}</td>
                                <td>{{ $term->category }}</td>
                                <td>
                                    @if($term->status == 'approved')
                                        <span class="badge bg-success">Approuvé</span>
                                    @elseif($term->status == 'candidate')
                                        <span class="badge bg-warning">Candidat</span>
                                    @elseif($term->status == 'deprecated')
                                        <span class="badge bg-danger">Obsolète</span>
                                    @endif
                                </td>
                                <td>{{ $term->notation }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('terms.show', $term->id) }}" class="btn btn-sm btn-info" title="Voir">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('terms.edit', $term->id) }}" class="btn btn-sm btn-primary" title="Modifier">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('terms.destroy', $term->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce terme?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Aucun terme trouvé.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Recherche de termes
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('termSearch');
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('tbody tr');

            tableRows.forEach(function(row) {
                const text = row.textContent.toLowerCase();
                if(text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
</script>
@endsection
