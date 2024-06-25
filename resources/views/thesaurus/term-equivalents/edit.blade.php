@extends('layouts.app')

@section('content')
    <h1>Edit Term equivalent for {{ $term->name }}</h1>
    <form action="{{ route('terms.term-equivalents.update', [$term, $termEquivalent]) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="child_id">Child Term</label>
            <select name="child_id" id="child_id" class="form-control">
                @foreach ($terms as $childTerm)
                    <option value="{{ $childTerm->id }}" {{ $childTerm->id == $termEquivalent->child_id ? 'selected' : '' }}>{{ $childTerm->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="equivalent_type_id">equivalent Type</label>
            <select name="equivalent_type_id" id="equivalent_type_id" class="form-control">
                @foreach ($equivalentTypes as $equivalentType)
                    <option value="{{ $equivalentType->id }}" {{ $equivalentType->id == $termEquivalent->equivalent_type_id ? 'selected' : '' }}>{{ $equivalentType->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
@endsection
