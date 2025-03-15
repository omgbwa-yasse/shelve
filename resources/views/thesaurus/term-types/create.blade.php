@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mt-4">{{ __('Create Term Type') }}</h1>
    <form action="{{ route('term-types.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="code" class="form-label">{{ __('Code') }}:</label>
            <input type="text" name="code" id="code" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">{{ __('Name') }}:</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">{{ __('Description') }}:</label>
            <textarea name="description" id="description" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
    </form>
</div>
@endsection
