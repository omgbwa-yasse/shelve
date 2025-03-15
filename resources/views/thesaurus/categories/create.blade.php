@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('Create Term Category') }}</h1>

    <form action="{{ route('term-categories.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">{{ __('Name') }}</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">{{ __('Description') }}</label>
            <textarea class="form-control" id="description" name="description"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
    </form>
</div>
@endsection
