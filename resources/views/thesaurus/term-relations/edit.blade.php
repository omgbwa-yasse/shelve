@extends('layouts.app')

@section('content')
    <h1>Edit Term Relation for {{ $term->name }}</h1>
    <form action="{{ route('terms.term-relations.update', [$term, $termRelation]) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="child_id">Child Term</label>
            <select name="child_id" id="child_id" class="form-control">
                @foreach ($terms as $childTerm)
                    <option value="{{ $childTerm->id }}" {{ $childTerm->id == $termRelation->child_id ? 'selected' : '' }}>{{ $childTerm->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="relation_type_id">Relation Type</label>
            <select name="relation_type_id" id="relation_type_id" class="form-control">
                @foreach ($relationTypes as $relationType)
                    <option value="{{ $relationType->id }}" {{ $relationType->id == $termRelation->relation_type_id ? 'selected' : '' }}>{{ $relationType->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
@endsection
