<!-- resources/views/bulletin-boards/edit.blade.php -->

@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Éditer le Babillard</h1>
        <form action="{{ route('bulletin-boards.update', $bulletinBoard) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Nom</label>
                <input type="text" name="name" class="form-control" value="{{ $bulletinBoard->name }}" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" class="form-control" required>{{ $bulletinBoard->description }}</textarea>
            </div>
            <div class="form-group">
                <label for="organisations">Organisations</label>
                <select name="organisations[]" class="form-control" multiple>
                    @foreach($organisations as $organisation)
                        <option value="{{ $organisation->id }}" {{ $bulletinBoard->organisations->contains($organisation) ? 'selected' : '' }}>
                            {{ $organisation->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-success">Mettre à jour</button>
        </form>
    </div>
@endsection
