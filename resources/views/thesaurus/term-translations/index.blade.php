@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('Term Translations') }}</h1>
    <a href="{{ route('term-translations.create', $term) }}" class="btn btn-primary">{{ __('Add Term Translation') }}</a>
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('Term') }}</th>
                <th>{{ __('Translation') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($termTranslations as $termTranslation)
                <tr>
                    <td>{{ $termTranslation->term->name }}</td>
                    <td>{{ $termTranslation->term2->name }}</td>
                    <td>
                        <a href="{{ route('term-translations.edit', [$term, $termTranslation]) }}" class="btn btn-sm btn-primary">{{ __('Edit') }}</a>
                        <form action="{{ route('term-translations.destroy', [$term, $termTranslation]) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ __('Are you sure you want to delete this term translation?') }}')">{{ __('Delete') }}</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
