@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('Edit Retention for Activity') }}: {{ $activity->name }}</h1>
    <form action="{{ route('activities.retentions.update', [$activity->id, $retentionActivity->id]) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="retention_id">{{ __('Retention') }}</label>
            <select class="form-control" id="retention_id" name="retention_id">
                @foreach($retentions as $retention)
                <option value="{{ $retention->id }}" {{ $retention->id == $retentionActivity->retention_id ? 'selected' : '' }}>
                    {{ $retention->name }}
                </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">{{ __('Update Retention') }}</button>
    </form>
</div>
@endsection
