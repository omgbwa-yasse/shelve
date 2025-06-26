@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Détails du participant</h2>
                    <div>
                        <a href="{{ route('public.chat-participants.edit', $participant) }}" class="btn btn-warning">Modifier</a>
                        <a href="{{ route('public.chat-participants.index') }}" class="btn btn-secondary">Retour</a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Informations du participant</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Utilisateur :</th>
                                    <td>{{ $participant->user->name ?? 'Inconnu' }}</td>
                                </tr>
                                <tr>
                                    <th>Email :</th>
                                    <td>{{ $participant->user->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Rôle :</th>
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
                                </tr>
                                <tr>
                                    <th>Statut :</th>
                                    <td>
                                        @if($participant->is_active)
                                            <span class="badge bg-success">Actif</span>
                                        @else
                                            <span class="badge bg-secondary">Inactif</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Date d'ajout :</th>
                                    <td>{{ $participant->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Dernière modification :</th>
                                    <td>{{ $participant->updated_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h5>Discussion</h5>
                            @if($participant->chat)
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $participant->chat->title ?? 'Discussion #' . $participant->chat->id }}</h6>

                                        @if($participant->chat->description)
                                            <p class="card-text">{{ Str::limit($participant->chat->description, 200) }}</p>
                                        @endif

                                        <p><strong>Type :</strong> {{ $participant->chat->type ?? 'N/A' }}</p>
                                        <p><strong>Statut :</strong> {{ $participant->chat->status ?? 'N/A' }}</p>
                                        <p><strong>Créée le :</strong> {{ $participant->chat->created_at->format('d/m/Y H:i') }}</p>

                                        <a href="{{ route('public.chats.show', $participant->chat) }}" class="btn btn-sm btn-outline-primary">Voir la discussion</a>
                                    </div>
                                </div>
                            @else
                                <p class="text-muted">Aucune discussion associée</p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5>Actions</h5>

                        @if($participant->is_active)
                            <form action="{{ route('public.chat-participants.update', $participant) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="is_active" value="0">
                                <button type="submit" class="btn btn-warning" onclick="return confirm('Êtes-vous sûr de vouloir désactiver ce participant ?')">Désactiver</button>
                            </form>
                        @else
                            <form action="{{ route('public.chat-participants.update', $participant) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="is_active" value="1">
                                <button type="submit" class="btn btn-success" onclick="return confirm('Êtes-vous sûr de vouloir réactiver ce participant ?')">Réactiver</button>
                            </form>
                        @endif

                        <form action="{{ route('public.chat-participants.destroy', $participant) }}" method="POST" class="d-inline ms-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir retirer définitivement ce participant ?')">Retirer du chat</button>
                        </form>
                    </div>

                    @if($participant->user && $participant->chat)
                        <div class="mt-4">
                            <h5>Statistiques</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h6 class="card-title">Messages envoyés</h6>
                                            <p class="card-text display-6">{{ $participant->chat->messages()->where('user_id', $participant->user_id)->count() }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h6 class="card-title">Dernière activité</h6>
                                            <p class="card-text">
                                                @php
                                                    $lastMessage = $participant->chat->messages()->where('user_id', $participant->user_id)->latest()->first();
                                                @endphp
                                                {{ $lastMessage ? $lastMessage->created_at->format('d/m/Y H:i') : 'Aucune activité' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h6 class="card-title">Temps dans le chat</h6>
                                            <p class="card-text">{{ $participant->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
