@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('Terms') }}</h1>
    <a href="{{ route('terms.create') }}" class="btn btn-primary mb-3">{{ __('Add Term') }}</a>
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('Code') }}</th>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Description') }}</th>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Parent') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($terms as $term)
                <tr>
                    <td>{{ $term->code }}</td>
                    <td>{{ $term->name }}</td>
                    <td>{{ $term->description }}</td>
                    <td>{{ $term->type->name }}</td>
                    <td>{{ $term->parent->name ?? __('Root Term') }}</td>
                    <td>
                        <a href="{{ route('terms.show', $term->id) }}" class="btn btn-info">{{ __('Settings') }}</a>
                        <a href="{{ route('terms.edit', $term->id) }}" class="btn btn-primary">{{ __('Edit') }}</a>
                        <form action="{{ route('terms.destroy', $term->id) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('{{ __('Are you sure you want to delete this term?') }}')">{{ __('Delete') }}</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
