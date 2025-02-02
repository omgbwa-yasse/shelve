<!-- resources/views/bulletin-boards/edit.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Modifier le tableau d'affichage</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('bulletin-boards.update', $bulletinBoard) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Nom</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $bulletinBoard->name) }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3">{{ old('description', $bulletinBoard->description) }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Organisations</label>
                            @foreach($organisations as $organisation)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="organisations[]"
                                       value="{{ $organisation->id }}" id="org{{ $organisation->id }}"
                                       @checked(in_array($organisation->id, old('organisations', $bulletinBoard->organisations->pluck('id')->toArray())))>
                                <label class="form-check-label" for="org{{ $organisation->id }}">
                                    {{ $organisation->name }}
                                </label>
                            </div>
                            @endforeach
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Mettre à jour
                            </button>
                            <a href="{{ route('bulletin-boards.show', $bulletinBoard) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-lg"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
