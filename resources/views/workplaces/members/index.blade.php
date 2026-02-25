@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('workplaces.partials.site-header', ['activeTab' => 'members'])

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0 text-muted"><i class="bi bi-people me-2"></i>Gérer les membres et les invitations</h5>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#inviteModal">
            <i class="bi bi-person-plus me-1"></i>Inviter un membre
        </button>
    </div>

    <!-- Current Members -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Membres actifs ({{ $members->count() }})</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Rôle</th>
                            <th>Permissions</th>
                            <th>Rejoint le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($members as $member)
                        <tr>
                            <td>
                                <i class="bi bi-person-circle"></i>
                                {{ $member->user->name }}
                                <br>
                                <small class="text-muted">{{ $member->user->email }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $member->role == 'owner' ? 'danger' : ($member->role == 'admin' ? 'primary' : 'secondary') }}">
                                    {{ ucfirst($member->role) }}
                                </span>
                            </td>
                            <td>
                                <small>
                                    @if($member->can_create_folders) <span class="badge bg-success">Dossiers</span> @endif
                                    @if($member->can_create_documents) <span class="badge bg-success">Documents</span> @endif
                                    @if($member->can_delete) <span class="badge bg-danger">Supprimer</span> @endif
                                    @if($member->can_share) <span class="badge bg-info">Partager</span> @endif
                                    @if($member->can_invite) <span class="badge bg-warning">Inviter</span> @endif
                                </small>
                            </td>
                            <td>{{ $member->joined_at->format('d/m/Y') }}</td>
                            <td>
                                @if($member->role != 'owner')
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editMemberModal{{ $member->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('workplaces.members.destroy', [$workplace, $member]) }}" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Confirmer la suppression ?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pending Invitations -->
    @if($invitations->count() > 0)
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Invitations en attente ({{ $invitations->count() }})</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Rôle proposé</th>
                            <th>Invité par</th>
                            <th>Expire le</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invitations as $invitation)
                        <tr>
                            <td>{{ $invitation->email }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($invitation->proposed_role) }}</span></td>
                            <td>{{ $invitation->inviter->name }}</td>
                            <td>{{ $invitation->expires_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($invitation->isExpired())
                                <span class="badge bg-danger">Expirée</span>
                                @else
                                <span class="badge bg-warning">En attente</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Invite Modal -->
<div class="modal fade" id="inviteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Inviter un membre</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('workplaces.members.store', $workplace) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                        <small class="text-muted">L'utilisateur recevra une invitation par email</small>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Rôle</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="viewer">Lecteur (lecture seule)</option>
                            <option value="contributor">Contributeur (peut ajouter du contenu)</option>
                            <option value="editor" selected>Éditeur (peut modifier)</option>
                            <option value="admin">Administrateur (tous les droits)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="message" class="form-label">Message (optionnel)</label>
                        <textarea class="form-control" id="message" name="message" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Envoyer l'invitation</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
