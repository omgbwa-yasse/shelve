@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Participants aux discussions</h2>
                    <a href="{{ route('public.chat-participants.create') }}" class="btn btn-primary">Ajouter un participant</a>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Discussion</th>
                                    <th>Utilisateur</th>
                                    <th>Rôle</th>
                                    <th>Statut</th>
                                    <th>Date d'ajout</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($participants as $participant)
                                    <tr>
                                        <td>{{ $participant->chat->title ?? 'Discussion #' . $participant->chat_id }}</td>
                                        <td>{{ $participant->user->name ?? 'Inconnu' }}</td>
                                        <td>
                                            @switch($participant->role)
                                                @case('admin')
                                                    <span class="badge bg-danger">Administrateur</span>
                                                    @break
                                                @case('moderator')
                                                    <span class="badge bg-warning">Modérateur</span>
                                                    @break
                                                @case('member')
                                                    <span class="badge bg-primary">Membre</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $participant->role }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            @if($participant->is_active)
                                                <span class="badge bg-success">Actif</span>
                                            @else
                                                <span class="badge bg-secondary">Inactif</span>
                                            @endif
                                        </td>
                                        <td>{{ $participant->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('public.chat-participants.show', $participant) }}" class="btn btn-info btn-sm">Voir</a>
                                            <a href="{{ route('public.chat-participants.edit', $participant) }}" class="btn btn-warning btn-sm">Modifier</a>
                                            <form action="{{ route('public.chat-participants.destroy', $participant) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir retirer ce participant ?')">Retirer</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{ $participants->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
