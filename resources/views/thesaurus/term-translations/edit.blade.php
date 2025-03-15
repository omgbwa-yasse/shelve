@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('Edit Term Translation') }}</h1>
    <form action="{{ route('term-translations.update', [$term, $termTranslation]) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="term2_id">{{ __('Term 2') }}</label>
            <select name="term2_id" id="term2_id" class="form-control" required>
                <option value="">{{ __('Select Term') }}</option>
                @foreach($terms as $term2)
                    <option value="{{ $term2->id }}" {{ $term2->id == $termTranslation->term2_id ? 'selected' : '' }}>{{ $term2->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
        <a href="{{ route('term-translations.index', $term) }}" class="btn btn-secondary">{{ __('Back') }}</a>
    </form>
</div>
@endsection
