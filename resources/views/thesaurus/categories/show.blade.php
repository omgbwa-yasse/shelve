@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ $category->name }}</h1>
    <a href="{{ route('term-categories.index') }}" class="btn btn-secondary btn-sm">{{ __('Back') }}</a>
    <a href="{{ route('term-categories.edit', $category->id) }}" class="btn btn-secondary btn-sm">{{ __('Edit') }}</a>
    <form action="{{ route('term-categories.destroy', $category->id) }}" method="POST" style="display: inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('{{ __('Are you sure you want to delete this term category?') }}')">{{ __('Delete') }}</button>
    </form>
</div>
@endsection
