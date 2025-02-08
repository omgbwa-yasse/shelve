<!-- resources/views/bulletin-boards/events/edit.blade.php -->

@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Éditer l'Événement</h1>
        <form action="{{ route('bulletin-boards.events.update', $event) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Nom</label>
                <input type="text" name="name" class="form-control" value="{{ $event->name }}" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" class="form-control" required>{{ $event->description }}</textarea>
            </div>
            <div class="form-group">
                <label for="start_date">Date de Début</label>
                <input type="date" name="start_date" class="form-control" value="{{ $event->start_date }}" required>
            </div>
            <div class="form-group">
                <label for="end_date">Date de Fin</label>
                <input type="date" name="end_date" class="form-control" value="{{ $event->end_date }}">
            </div>
            <div class="form-group">
                <label for="location">Lieu</label>
                <input type="text" name="location" class="form-control" value="{{ $event->location }}">
            </div>
            <div class="form-group">
                <label for="organisations">Organisations</label>
                <select name="organisations[]" class="form-control" multiple>
                    @foreach($organisations as $organisation)
                        <option value="{{ $organisation->id }}" {{ $event->bulletinBoard->organisations->contains($organisation) ? 'selected' : '' }}>
                            {{ $organisation->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-success">Mettre à jour</button>
        </form>
    </div>
@endsection
