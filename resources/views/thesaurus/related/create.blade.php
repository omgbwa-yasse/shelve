@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('Add Related Term for') }} "{{ $term->name }}"</h1>
    <form action="{{ route('term-related.store', $term) }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="term_related_id" class="form-label">Related Term</label>
            <select name="term_related_id" id="term_related_id" class="form-select" required>
                <option value="">Select a term</option>
                @foreach ($terms as $t)
                <option value="{{ $t->id }}">{{ $t->name }}</option>
                @endforeach
            </select>
            @error('term_related_id')
            <div class="text-danger">{{ __($message) }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Add Related Term</button>
    </form>
</div>
@endsection
