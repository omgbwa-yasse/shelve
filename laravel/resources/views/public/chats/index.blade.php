@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Conversations</h2>
                    <a href="{{ route('public.chats.create') }}" class="btn btn-primary">Nouvelle conversation</a>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row">
                        @forelse($chats as $chat)
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $chat->title }}</h5>
                                        <p class="card-text text-muted">
                                            Dernier message: {{ $chat->last_message ? $chat->last_message->created_at->diffForHumans() : 'Aucun message' }}
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-{{ $chat->is_active ? 'success' : 'secondary' }}">
                                                {{ $chat->is_active ? 'Active' : 'Archivée' }}
                                            </span>
                                            <div>
                                                <a href="{{ route('public.chats.show', $chat) }}" class="btn btn-info btn-sm">Voir</a>
                                                @if($chat->is_active)
                                                    <form action="{{ route('public.chats.destroy', $chat) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir archiver cette conversation ?')">Archiver</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info">
                                    Vous n'avez aucune conversation. Commencez-en une nouvelle !
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
