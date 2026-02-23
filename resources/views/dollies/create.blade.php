@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Ajouter un Chariot</h1>
    <form action="{{ route('dolly.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control" required></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Catégorie</label>
            <div class="row">
                @foreach ($categories as $category)
                    <div class="col-md-4 mb-2">
                        <div class="form-check">
                            <input class="form-check-input category-radio" type="radio" name="category" id="category_{{ $category }}" value="{{ $category }}" required>
                            <label class="form-check-label" for="category_{{ $category }}">
                                @if($category == 'record')
                                    <i class="bi bi-archive text-primary"></i> Description des archives
                                @elseif($category == 'mail')
                                    <i class="bi bi-envelope text-primary"></i> Courrier
                                @elseif($category == 'communication')
                                    <i class="bi bi-chat-dots text-primary"></i> Communication des archives
                                @elseif($category == 'room')
                                    <i class="bi bi-door-open text-primary"></i> Salle d'archives
                                @elseif($category == 'building')
                                    <i class="bi bi-building text-primary"></i> Bâtiments d'archives
                                @elseif($category == 'container')
                                    <i class="bi bi-box-seam text-primary"></i> Boites et chronos
                                @elseif($category == 'shelf')
                                    <i class="bi bi-bookshelf text-primary"></i> Étagère
                                @elseif($category == 'slip')
                                    <i class="bi bi-arrow-left-right text-primary"></i> Versement
                                @elseif($category == 'slip_record')
                                    <i class="bi bi-file-earmark-arrow-up text-primary"></i> Description de versement
                                @elseif($category == 'digital_folder')
                                    <i class="bi bi-folder-plus text-primary"></i> Dossiers Numériques
                                @elseif($category == 'digital_document')
                                    <i class="bi bi-file-earmark-text text-success"></i> Documents Numériques
                                @elseif($category == 'artifact')
                                    <i class="bi bi-gem text-warning"></i> Artefacts
                                @else
                                    <i class="bi bi-cart3 text-primary"></i> {{ ucfirst(str_replace('_', ' ', $category)) }}
                                @endif
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</div>
@endsection
