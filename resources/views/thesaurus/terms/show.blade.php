@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('Term Details') }}</h1>
    <table class="table">
        <tr>
            <th>{{ __('Name') }}</th>
            <td>{{ $term->name }}</td>
        </tr>
        <tr>
            <th>{{ __('Description') }}</th>
            <td>{{ $term->description }}</td>
        </tr>
        <tr>
            <th>{{ __('Type') }}</th>
            <td>{{ $term->type->name }}</td>
        </tr>
        <tr>
            <th>{{ __('Parent') }}</th>
            <td>{{ $term->parent->name ?? __('Root Term') }}</td>
        </tr>
        <tr>
            <th>{{ __('Category') }}</th>
            <td>{{ $term->category->name }}</td>
        </tr>
        <tr>
            <th>{{ __('Language') }}</th>
            <td>{{ $term->language->name }}</td>
        </tr>
    </table>

    <div class="mt-3">
        <a href="{{ route('terms.index') }}" class="btn btn-secondary">{{ __('Back') }}</a>
        <a href="{{ route('terms.edit', $term->id) }}" class="btn btn-primary">{{ __('Edit') }}</a>
        <form action="{{ route('terms.destroy', $term->id) }}" method="POST" style="display: inline-block;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('{{ __('Are you sure you want to delete this term?') }}')">{{ __('Delete') }}</button>
        </form>
        <a href="{{ route('term-translations.create', $term) }}" class="btn btn-primary">{{ __('Add Translation') }}</a>
        <a href="{{ route('term-related.create', $term) }}" class="btn btn-primary">{{ __('Add Equivalent') }}</a>
    </div>
</div>
@endsection
