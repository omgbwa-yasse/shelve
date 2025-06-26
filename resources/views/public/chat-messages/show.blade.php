@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Détails du message</h2>
                    <div>
                        <a href="{{ route('public.chat-messages.edit', $message) }}" class="btn btn-warning">Modifier</a>
                        <a href="{{ route('public.chat-messages.index') }}" class="btn btn-secondary">Retour</a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5>Contenu du message</h5>
                            <div class="card">
                                <div class="card-body">
                                    @if($message->type === 'image' && $message->file_path)
                                        <img src="{{ Storage::url($message->file_path) }}" alt="Message attaché" class="img-fluid mb-3" style="max-height: 400px;">
                                    @endif

                                    @if($message->content)
                                        <p>{{ $message->content }}</p>
                                    @endif

                                    @if($message->type === 'file' && $message->file_path)
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-file me-2"></i>
                                            <a href="{{ Storage::url($message->file_path) }}" target="_blank">{{ basename($message->file_path) }}</a>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if($message->parent)
                                <div class="mt-4">
                                    <h6>En réponse à :</h6>
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <small class="text-muted">{{ $message->parent->user->name ?? 'Inconnu' }} - {{ $message->parent->created_at->format('d/m/Y H:i') }}</small>
                                            <p class="mb-0">{{ Str::limit($message->parent->content, 200) }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @php
                                $replies = $message->replies()->with('user')->latest()->take(5)->get();
                            @endphp
                            @if($replies->count() > 0)
                                <div class="mt-4">
                                    <h6>Réponses ({{ $message->replies()->count() }}) :</h6>
                                    @foreach($replies as $reply)
                                        <div class="card mb-2">
                                            <div class="card-body py-2">
                                                <small class="text-muted">{{ $reply->user->name ?? 'Inconnu' }} - {{ $reply->created_at->format('d/m/Y H:i') }}</small>
                                                <p class="mb-0">{{ Str::limit($reply->content, 150) }}</p>
                                                <a href="{{ route('public.chat-messages.show', $reply) }}" class="btn btn-sm btn-outline-primary">Voir</a>
                                            </div>
                                        </div>
                                    @endforeach

                                    @if($message->replies()->count() > 5)
                                        <p class="text-muted">{{ $message->replies()->count() - 5 }} réponse(s) supplémentaire(s)...</p>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <h5>Informations</h5>
                            <table class="table table-bordered table-sm">
                                <tr>
                                    <th>Auteur :</th>
                                    <td>{{ $message->user->name ?? 'Inconnu' }}</td>
                                </tr>
                                <tr>
                                    <th>Email :</th>
                                    <td>{{ $message->user->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Type :</th>
                                    <td>
                                        @switch($message->type)
                                            @case('text')
                                                <span class="badge bg-primary">Texte</span>
                                                @break
                                            @case('file')
                                                <span class="badge bg-info">Fichier</span>
                                                @break
                                            @case('image')
                                                <span class="badge bg-success">Image</span>
                                                @break
                                            @case('system')
                                                <span class="badge bg-warning">Système</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ $message->type }}</span>
                                        @endswitch
                                    </td>
                                </tr>
                                <tr>
                                    <th>Date de création :</th>
                                    <td>{{ $message->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Dernière modification :</th>
                                    <td>{{ $message->updated_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                @if($message->file_path)
                                    <tr>
                                        <th>Fichier :</th>
                                        <td>{{ basename($message->file_path) }}</td>
                                    </tr>
                                @endif
                            </table>

                            <h5 class="mt-4">Discussion</h5>
                            @if($message->chat)
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $message->chat->title ?? 'Discussion #' . $message->chat->id }}</h6>
                                        <p><strong>Type :</strong> {{ $message->chat->type ?? 'N/A' }}</p>
                                        <p><strong>Statut :</strong> {{ $message->chat->status ?? 'N/A' }}</p>
                                        <a href="{{ route('public.chats.show', $message->chat) }}" class="btn btn-sm btn-outline-primary">Voir la discussion</a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5>Actions</h5>
                        <a href="{{ route('public.chat-messages.create') }}?reply_to={{ $message->id }}" class="btn btn-success">Répondre</a>

                        <form action="{{ route('public.chat-messages.destroy', $message) }}" method="POST" class="d-inline ms-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?')">Supprimer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
