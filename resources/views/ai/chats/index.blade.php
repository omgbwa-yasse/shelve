@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">Chats AI</h2>
                        <a href="{{ route('ai.chats.create') }}" class="btn btn-primary">Nouveau Chat</a>
                    </div>

                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Titre</th>
                                        <th>Utilisateur</th>
                                        <th>Modèle AI</th>
                                        <th>Statut</th>
                                        <th>Créé le</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($chats as $chat)
                                        <tr>
                                            <td>{{ $chat->id }}</td>
                                            <td>{{ $chat->title }}</td>
                                            <td>{{ $chat->user->name }}</td>
                                            <td>{{ $chat->aiModel->name }}</td>
                                            <td>
                                                <span class="badge bg-{{ $chat->is_active ? 'success' : 'danger' }}">
                                                    {{ $chat->is_active ? 'Actif' : 'Inactif' }}
                                                </span>
                                            </td>
                                            <td>{{ $chat->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('ai.chats.show', $chat) }}"
                                                       class="btn btn-sm btn-info" title="Voir">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('ai.chats.edit', $chat) }}"
                                                       class="btn btn-sm btn-warning" title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('ai.chats.destroy', $chat) }}"
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce chat ?')"
                                                                title="Supprimer">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $chats->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
