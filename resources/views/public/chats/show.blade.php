@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>{{ $chat->title }}</h2>
                    <div>
                        @if($chat->is_active)
                            <form action="{{ route('public.chats.destroy', $chat) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir archiver cette conversation ?')">Archiver</button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <div class="chat-messages mb-4" id="chat-messages">
                        @foreach($messages as $message)
                            <div class="message {{ $message->user_id === auth()->id() ? 'message-sent' : 'message-received' }} mb-3">
                                <div class="message-content p-3 rounded {{ $message->user_id === auth()->id() ? 'bg-primary text-white' : 'bg-light' }}">
                                    <div class="message-header d-flex justify-content-between align-items-center mb-2">
                                        <strong>{{ $message->user->name }}</strong>
                                        <small class="text-{{ $message->user_id === auth()->id() ? 'light' : 'muted' }}">
                                            {{ $message->created_at->format('d/m/Y H:i') }}
                                        </small>
                                    </div>
                                    <div class="message-body">
                                        {{ $message->content }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($chat->is_active)
                        <form action="{{ route('public.chats.messages.store', $chat) }}" method="POST" class="message-form">
                            @csrf
                            <div class="input-group">
                                <textarea class="form-control @error('content') is-invalid @enderror"
                                          name="content"
                                          rows="2"
                                          placeholder="Écrivez votre message..."
                                          required></textarea>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Envoyer
                                </button>
                            </div>
                            @if($errors->has('content'))
                                <div class="invalid-feedback d-block">
                                    {{ $errors->first('content') }}
                                </div>
                            @endif
                        </form>
                    @else
                        <div class="alert alert-info">
                            Cette conversation est archivée. Vous ne pouvez plus envoyer de messages.
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h3>Participants</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        @foreach($chat->participants as $participant)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $participant->user->name }}
                                @if($participant->is_admin)
                                    <span class="badge bg-primary">Admin</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .chat-messages {
        max-height: 500px;
        overflow-y: auto;
    }
    .message {
        display: flex;
        flex-direction: column;
    }
    .message-sent {
        align-items: flex-end;
    }
    .message-received {
        align-items: flex-start;
    }
    .message-content {
        max-width: 80%;
    }
</style>
@endpush

@push('scripts')
<script>
    const chatMessages = document.getElementById('chat-messages');
    chatMessages.scrollTop = chatMessages.scrollHeight;

    const textarea = document.querySelector('textarea');
    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
</script>
@endpush
@endsection
