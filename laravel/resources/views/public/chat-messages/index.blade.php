@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Messages des discussions</h2>
                    <a href="{{ route('public.chat-messages.create') }}" class="btn btn-primary">Nouveau message</a>
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
                                    <th>Message</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($messages as $message)
                                    <tr>
                                        <td>{{ $message->chat->title ?? 'Discussion #' . $message->chat_id }}</td>
                                        <td>{{ $message->user->name ?? 'Inconnu' }}</td>
                                        <td>{{ Str::limit($message->content, 100) }}</td>
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
                                        <td>{{ $message->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('public.chat-messages.show', $message) }}" class="btn btn-info btn-sm">Voir</a>
                                            <a href="{{ route('public.chat-messages.edit', $message) }}" class="btn btn-warning btn-sm">Modifier</a>
                                            <form action="{{ route('public.chat-messages.destroy', $message) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?')">Supprimer</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{ $messages->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
