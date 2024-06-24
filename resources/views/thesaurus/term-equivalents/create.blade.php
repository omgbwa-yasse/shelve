@extends('layouts.app')

@section('content')
    <h1>Add Term Equivalent for {{ $term->name }}</h1>

    <form action="{{ route('term-equivalents.store', $term) }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="term2_id">Term 2</label>
            <select name="term2_id" id="term2_id" class="form-control" required>
                <option value="">Select a term</option>
                @foreach (\App\Models\Term::all() as $term2)
                    <option value="{{ $term2->id }}">{{ $term2->name }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Save</button>
    </form>
@endsection
