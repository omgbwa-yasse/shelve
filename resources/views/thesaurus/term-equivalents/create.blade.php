@extends('layouts.app')

@section('content')
    <h1>Etablir une equivalent </h1>
    Terme à associer <button type="button" class="btn btn-danger">{{ $term->name }}</button>

    <form action="{{ route('term-equivalents.store', $term) }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="equivalent_type_id">equivalent Type</label>
            <select name="equivalent_type_id" id="equivalent_type_id" class="form-control">
                @foreach ($equivalentTypes as $equivalentType)
                    <option value="{{ $equivalentType->id }}">{{ $equivalentType->name }}</option>
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
