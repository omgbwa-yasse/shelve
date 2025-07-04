@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4>Organisations Externes</h4>
                    </div>
                    <div>
                        <a href="{{ route('external.organizations.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Nouvelle Organisation
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
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Forme juridique</th>
                                    <th>Contact principal</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Ville</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($organizations as $org)
                                <tr>
                                    <td>{{ $org->name }}</td>
                                    <td>{{ $org->legal_form ?? '-' }}</td>
                                    <td>
                                        @if($org->primaryContact)
                                            <a href="{{ route('external.contacts.show', $org->primaryContact->id) }}">
                                                {{ $org->primaryContact->full_name }}
                                            </a>
                                        @else
                                            <span class="text-muted">Aucun</span>
                                        @endif
                                    </td>
                                    <td>{{ $org->email ?? '-' }}</td>
                                    <td>{{ $org->phone ?? '-' }}</td>
                                    <td>{{ $org->city ?? '-' }}</td>
                                    <td>
                                        @if($org->is_verified)
                                            <span class="badge bg-success">Vérifié</span>
                                        @else
                                            <span class="badge bg-warning">Non vérifié</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('external.organizations.show', $org->id) }}" class="btn btn-sm btn-info">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="{{ route('external.organizations.edit', $org->id) }}" class="btn btn-sm btn-primary">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <form action="{{ route('external.organizations.destroy', $org->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette organisation ?')">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">Aucune organisation externe trouvée</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $organizations->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
