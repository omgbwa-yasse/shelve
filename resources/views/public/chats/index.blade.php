@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Public Chats') }}</h5>
                    <a href="{{ route('public.chats.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> {{ __('New Chat') }}
                    </a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Participants') }}</th>
                                    <th>{{ __('Last Message') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Created') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($chats as $chat)
                                    <tr>
                                        <td>{{ $chat->title }}</td>
                                        <td>{{ $chat->participants_count }}</td>
                                        <td>
                                            @if($chat->last_message)
                                                <small>{{ Str::limit($chat->last_message->content, 50) }}</small>
                                                <br>
                                                <small class="text-muted">{{ $chat->last_message->created_at->diffForHumans() }}</small>
                                            @else
                                                <small class="text-muted">{{ __('No messages yet') }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $chat->is_active ? 'success' : 'danger' }}">
                                                {{ $chat->is_active ? __('Active') : __('Archived') }}
                                            </span>
                                        </td>
                                        <td>{{ $chat->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('public.chats.show', $chat) }}" class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('public.chats.edit', $chat) }}" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="{{ route('public.chats.destroy', $chat) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ __('Are you sure?') }}')">
                                                        <i class="bi bi-trash"></i>
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
