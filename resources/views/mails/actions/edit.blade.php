@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('Edit Mail Action') }}</h1>
    <form method="POST" action="{{ route('mail-actions.update', $mailAction->id) }}">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">{{ __('mail_action.name') }}</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $mailAction->name }}" required>
        </div>
        <div class="mb-3">
            <label for="duration" class="form-label">{{ __('mail_action.duration') }}</label>
            <input type="number" name="duration" id="duration" class="form-control" value="{{ $mailAction->duration }}" required>
        </div>
        <div class="mb-3">
            <label for="to_return" class="form-label">{{ __('mail_action.to_return') }}</label>
            <select name="to_return" id="to_return" class="form-select">
                <option value="1" {{ $mailAction->to_return ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ !$mailAction->to_return ? 'selected' : '' }}>No</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">{{ __('Description') }}</label>
            <textarea name="description" id="description" class="form-control" >{{ $mailAction->description }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">{{ __('mail_action.update') }}</button>
    </form>
</div>
@endsection
