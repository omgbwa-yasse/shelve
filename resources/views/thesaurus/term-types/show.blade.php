@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mt-4">{{ $termType->name }}</h1>
    <p>{{ $termType->description }}</p>
    <a href="{{ route('term-types.index') }}" class="btn btn-secondary">{{ __('Back') }}</a>
    <a href="{{ route('term-types.edit', $termType->id) }}" class="btn btn-primary">{{ __('Edit') }}</a>
    <form action="{{ route('term-types.destroy', $termType->id) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">{{ __('Delete') }}</button>
    </form>
</div>
@endsection
