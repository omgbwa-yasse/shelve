@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $event->title }}</h5>
                    <div>
                        <a href="{{ route('public.events.edit', $event->id) }}" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> {{ __('Edit Event') }}
                        </a>
                        <form action="{{ route('public.events.destroy', $event->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('{{ __('Are you sure you want to delete this event?') }}')">
                                <i class="bi bi-trash"></i> {{ __('Delete Event') }}
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-8">
                            <!-- Description -->
                            <div class="mb-4">
                                <h6 class="text-muted">{{ __('Description') }}</h6>
                                <p>{{ $event->description ?: __('No description provided.') }}</p>
                            </div>

                            <!-- Date and Time -->
                            <div class="mb-4">
                                <h6 class="text-muted">{{ __('Date and Time') }}</h6>
                                <p>
                                    <strong>{{ __('Start') }}:</strong> {{ $event->start_date->format('F j, Y g:i A') }}<br>
                                    <strong>{{ __('End') }}:</strong> {{ $event->end_date->format('F j, Y g:i A') }}
                                </p>
                            </div>

                            <!-- Location -->
                            <div class="mb-4">
                                <h6 class="text-muted">{{ __('Location') }}</h6>
                                <p>{{ $event->location ?: __('No location specified.') }}</p>
                            </div>

                            <!-- Status -->
                            <div class="mb-4">
                                <h6 class="text-muted">{{ __('Status') }}</h6>
                                <span class="badge badge-{{ $event->status === 'published' ? 'success' : ($event->status === 'draft' ? 'warning' : 'danger') }}">
                                    {{ __(ucfirst($event->status)) }}
                                </span>
                            </div>

                            <!-- Participants -->
                            <div class="mb-4">
                                <h6 class="text-muted">{{ __('Participants') }}</h6>
                                <p>
                                    <strong>{{ __('Registered') }}:</strong> {{ $event->registrations_count ?? 0 }}<br>
                                    <strong>{{ __('Maximum') }}:</strong> {{ $event->max_participants ?: __('Unlimited') }}
                                </p>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- Event Information -->
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">{{ __('Event Information') }}</h6>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            <strong>{{ __('Created') }}:</strong> {{ $event->created_at->format('Y-m-d H:i') }}<br>
                                            <strong>{{ __('Last Updated') }}:</strong> {{ $event->updated_at->format('Y-m-d H:i') }}
                                        </small>
                                    </p>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="mt-4">
                                <a href="{{ route('public.events.index') }}" class="btn btn-secondary btn-block">
                                    <i class="bi bi-arrow-left"></i> {{ __('Back to Events') }}
                                </a>
                                @if($event->status === 'published')
                                    <a href="{{ route('public.events.register', $event->id) }}" class="btn btn-success btn-block">
                                        <i class="bi bi-person-plus"></i> {{ __('Register for Event') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
