@extends('layouts.app')

@section('content')
    <h1>{{ __('Assign Communicability Period') }}</h1>
    {{ __('Activity') }}: <strong>{{ $activity->name }}</strong>
    <form action="{{ route('activities.communicabilities.store', $activity->id) }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">{{ __('Choose Period') }}</label>
            <select name="communicability_id" id="communicability_id" class="form-select" required>
                <option value="">{{ __('Select a period') }}</option>
                @foreach ($communicabilities as $communicability)
                    <option value="{{ $communicability->id }}">{{ $communicability->code }} - {{ $communicability->name }}: {{ $communicability->description }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
    </form>
@endsection
