@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Related Term for "{{ $term->name }}"</h1>
    <form action="{{ route('term-related.update', [$term, $relatedTerm]) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="term_related_id" class="form-label">Related Term</label>
            <select name="term_related_id" id="term_related_id" class="form-select" required>
                <option value="">Select a term</option>
                @foreach ($terms as $t)
                <option value="{{ $t->id }}" {{ $t->id == $relatedTerm->relatedTerm->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
            @error('term_related_id')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Update Related Term</button>
    </form>
</div>
@endsection
