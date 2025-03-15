@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-3">
        <a href="{{ route('term-types.create') }}" class="btn btn-primary mb-3">{{ __('Create New Term Type') }}</a>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('Code') }}</th>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Description') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($termTypes as $termType)
                <tr>
                    <td>{{ $termType->code }}</td>
                    <td>{{ $termType->name }}</td>
                    <td>{{ $termType->description }}</td>
                    <td>
                        <a href="{{ route('term-types.show', $termType->id) }}" class="btn btn-secondary">{{ __('View') }}</a>
                        <a href="{{ route('term-types.edit', $termType->id) }}" class="btn btn-primary">{{ __('Edit') }}</a>
                        <form action="{{ route('term-types.destroy', $termType->id) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">{{ __('Delete') }}</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
