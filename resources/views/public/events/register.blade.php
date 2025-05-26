@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Register for Event') }}: {{ $event->title }}</h5>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="mb-4">
                        <h6 class="text-muted">{{ __('Event Details') }}</h6>
                        <p>
                            <strong>{{ __('Date') }}:</strong> {{ $event->start_date->format('F j, Y g:i A') }}<br>
                            <strong>{{ __('Location') }}:</strong> {{ $event->location }}<br>
                            <strong>{{ __('Available Spots') }}:</strong>
                            {{ $event->max_participants ? ($event->max_participants - ($event->registrations_count ?? 0)) : __('Unlimited') }}
                        </p>
                    </div>

                    <form method="POST" action="{{ route('public.event-registrations.store') }}">
                        @csrf
                        <input type="hidden" name="event_id" value="{{ $event->id }}">

                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Full Name') }}</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('Email') }}</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">{{ __('Phone Number') }}</label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone" value="{{ old('phone') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">{{ __('Additional Notes') }}</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                            <small class="form-text text-muted">{{ __('Optional: Any special requirements or questions') }}</small>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('public.events.show', $event->id) }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> {{ __('Back to Event') }}
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-person-plus"></i> {{ __('Complete Registration') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
