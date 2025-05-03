@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Public Events') }}</h5>
                    <a href="{{ route('public.events.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> {{ __('New Event') }}
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
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Location') }}</th>
                                    <th>{{ __('Registrations') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($events as $event)
                                    <tr>
                                        <td>{{ $event->title }}</td>
                                        <td>
                                            {{ $event->start_date->format('Y-m-d H:i') }}
                                            @if($event->end_date)
                                                <br>
                                                <small class="text-muted">{{ __('to') }} {{ $event->end_date->format('Y-m-d H:i') }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $event->location }}</td>
                                        <td>
                                            {{ $event->registrations_count }} / {{ $event->max_participants ?: '∞' }}
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $event->status === 'published' ? 'success' : ($event->status === 'draft' ? 'warning' : 'secondary') }}">
                                                {{ __(ucfirst($event->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('public.events.show', $event) }}" class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('public.events.edit', $event) }}" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="{{ route('public.events.destroy', $event) }}" method="POST" class="d-inline">
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
                        {{ $events->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
