@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $chat->title }}</h5>
                    <div class="btn-group" role="group">
                        <a href="{{ route('public.chats.edit', $chat) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> {{ __('Edit') }}
                        </a>
                        <form action="{{ route('public.chats.destroy', $chat) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('{{ __('Are you sure?') }}')">
                                <i class="bi bi-trash"></i> {{ __('Delete') }}
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="mb-4">
                        <h6>{{ __('Description') }}</h6>
                        <p>{{ $chat->description ?: __('No description provided.') }}</p>
                    </div>

                    <div class="mb-4">
                        <h6>{{ __('Status') }}</h6>
                        <span class="badge badge-{{ $chat->is_active ? 'success' : 'danger' }}">
                            {{ $chat->is_active ? __('Active') : __('Archived') }}
                        </span>
                    </div>

                    <div class="mb-4">
                        <h6>{{ __('Participants') }}</h6>
                        <div class="list-group">
                            @forelse($chat->participants as $participant)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $participant->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $participant->email }}</small>
                                    </div>
                                    <span class="badge badge-info">{{ $participant->pivot->role }}</span>
                                </div>
                            @empty
                                <div class="list-group-item text-muted">{{ __('No participants yet.') }}</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6>{{ __('Messages') }}</h6>
                        <div class="list-group">
                            @forelse($chat->messages as $message)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong>{{ $message->user->name }}</strong>
                                        <small class="text-muted">{{ $message->created_at->format('Y-m-d H:i') }}</small>
                                    </div>
                                    <p class="mb-0">{{ $message->content }}</p>
                                </div>
                            @empty
                                <div class="list-group-item text-muted">{{ __('No messages yet.') }}</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6>{{ __('Chat Information') }}</h6>
                        <ul class="list-unstyled">
                            <li><strong>{{ __('Created') }}:</strong> {{ $chat->created_at->format('Y-m-d H:i') }}</li>
                            <li><strong>{{ __('Last Updated') }}:</strong> {{ $chat->updated_at->format('Y-m-d H:i') }}</li>
                            <li><strong>{{ __('Total Messages') }}:</strong> {{ $chat->messages_count }}</li>
                            <li><strong>{{ __('Total Participants') }}:</strong> {{ $chat->participants_count }}</li>
                        </ul>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('public.chats.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> {{ __('Back to List') }}
                        </a>
                        <a href="{{ route('public.chats.edit', $chat) }}" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> {{ __('Edit Chat') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
