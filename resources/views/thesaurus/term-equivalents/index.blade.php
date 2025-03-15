@extends('layouts.app')

@section('content')
<a href="{{ route('terms.term-equivalents.create', $term) }}" class="btn btn-primary">{{ __('Create Term Equivalent') }}</a>
<table class="table">
    <thead>
        <tr>
            <th>{{ __('Term') }}</th>
            <th>{{ __('Child Term') }}</th>
            <th>{{ __('Equivalent Type') }}</th>
            <th>{{ __('Actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($termEquivalents as $termEquivalent)
        <tr>
            <td>{{ $termEquivalent->term->name }}</td>
            <td>{{ $termEquivalent->child->name }}</td>
            <td>{{ $termEquivalent->equivalentType->name }}</td>
            <td>
                <a href="{{ route('terms.term-equivalents.edit', [$term, $termEquivalent]) }}" class="btn btn-sm btn-primary">{{ __('Edit') }}</a>
                <form action="{{ route('terms.term-equivalents.destroy', [$term, $termEquivalent]) }}" method="POST" style="display: inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ __('Are you sure you want to delete this term equivalent?') }}')">{{ __('Delete') }}</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
