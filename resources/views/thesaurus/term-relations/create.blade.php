@extends('layouts.app')

@section('content')
    <h1>Etablir une relation </h1>
    Terme à associer <button type="button" class="btn btn-danger">{{ $term->name }}</button>

    <form action="{{ route('term-relations.store', $term) }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="relation_type_id">Relation Type</label>
            <select name="relation_type_id" id="relation_type_id" class="form-control">
                @foreach ($relationTypes as $relationType)
                    <option value="{{ $relationType->id }}">{{ $relationType->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="child_id"> terms associés </label>
            <input type="text" name="child_id" id="child_id" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Create</button>
    </form>
@endsection
