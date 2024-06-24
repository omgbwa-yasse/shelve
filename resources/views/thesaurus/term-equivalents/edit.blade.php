@extends('layouts.app')

@section('content')
    <h1>Edit Term Equivalent for {{ $term->name }}</h1>

    <form action="{{ route('term-equivalents.update', [$term, $termEquivalent]) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="term2_id">Term 2</label>
            <select name="term2_id" id="term2_id" class="form-control" required>
                <option value="">Select a term</option>
                @foreach (\App\Models\Term::all() as $term2)
                    <option value="{{ $term2->id }}" {{ $term2->id == $termEquivalent->term2_id ? 'selected' : '' }}>{{ $term2->name }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Save</button>
    </form>
@endsection
