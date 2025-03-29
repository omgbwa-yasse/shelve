@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('bulletin-boards.index') }}">Tableaux d'affichage</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('bulletin-boards.show', $bulletinBoard->id) }}">{{ $bulletinBoard->name }}</a></li>
                    <li class="breadcrumb-item active">Gérer les utilisateurs</li>
                </ol>
            </nav>

            <h2>Gérer les utilisateurs du tableau d'affichage</h2>
            <p class="text-muted">{{ $bulletinBoard->name }}</p>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Ajouter un utilisateur</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('bulletin-boards.add-user', $bulletinBoard->id) }}" method="POST">
                                @csrf

                                <div class="mb-3">
                                    <label for="user_id" class="form-label">Utilisateur</label>
                                    <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                                        <option value="">Sélectionner un utilisateur</option>
                                        @foreach(App\Models\User::whereHas('organisations', function($query) {
                                            $query->where('organisations.id', Auth::user()->current_organisation_id);
                                        })->orderBy('name')->get() as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="role" class="form-label">Rôle</label>
                                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                        <option value="moderator">Modérateur</option>
                                        <option value="admin">Administrateur</option>
                                        <option value="super_admin">Super Administrateur</option>
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="permissions" class="form-label">Permissions</label>
                                    <select class="form-select @error('permissions') is-invalid @enderror" id="permissions" name="permissions" required>
                                        <option value="write">Écriture</option>
                                        <option value="edit">Modification</option>
                                        <option value="delete">Suppression</option>
                                    </select>
                                    @error('permissions')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-user-plus me-1"></i> Ajouter
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">À propos des rôles</h5>
                        </div>
                        <div class="card-body">
                            <dl>
                                <dt>Super Administrateur</dt>
                                <dd>Peut tout faire, y compris supprimer le tableau d'affichage et gérer tous les utilisateurs.</dd>

                                <dt>Administrateur</dt>
                                <dd>Peut créer et gérer le contenu, mais ne peut pas supprimer le tableau ou gérer les super administrateurs.</dd>

                                <dt>Modérateur</dt>
                                <dd>Peut uniquement gérer le contenu selon les permissions attribuées.</dd>
                            </dl>

                            <p class="mb-0 text-muted"><strong>Note:</strong> Le dernier super administrateur ne peut pas être supprimé.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Utilisateurs actuels</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Rôle</th>
                                    <th>Permissions</th>
                                    <th>Ajouté par</th>
                                    <th>Depuis</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bulletinBoard->users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <div class="avatar-circle bg-primary text-white">
                                                        {{ substr($user->name, 0, 1) }}
                                                    </div>
                                                </div>
                                                <div>
                                                    {{ $user->name }}
                                                    @if($user->id == $bulletinBoard->created_by)
                                                        <span class="badge bg-info ms-1">Créateur</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $user->pivot->role == 'super_admin' ? 'danger' : ($user->pivot->role == 'admin' ? 'primary' : 'success') }}">
                                                {{ ucfirst($user->pivot->role) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ ucfirst($user->pivot->permissions) }}</span>
                                        </td>
                                        <td>
                                            {{ App\Models\User::find($user->pivot->assigned_by)->name ?? 'Inconnu' }}
                                        </td>
                                        <td>{{ $user->pivot->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            @if(Auth::id() != $user->id || $bulletinBoard->users()->wherePivot('role', 'super_admin')->count() > 1)

                                                <form action="{{ route('bulletin-boards.remove-user', [$bulletinBoard->id, $user->id]) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Êtes-vous sûr de vouloir retirer cet utilisateur ?')">
                                                        <i class="fas fa-user-minus"></i> Retirer
                                                    </button>
                                                </form>
                                            @else
                                                <button class="btn btn-sm btn-outline-secondary" disabled title="Vous ne pouvez pas vous retirer vous-même si vous êtes le dernier super administrateur">
                                                    <i class="fas fa-user-minus"></i> Retirer
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('bulletin-boards.show', $bulletinBoard->id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Retour au tableau d'affichage
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
}
.avatar-circle {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}
</style>
@endsection
