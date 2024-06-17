@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Créer un courrier</h1>
    <form action="{{ route('mail-author.store') }}" method="POST">
        @csrf

        {{-- Champs de saisie --}}
        <div class="mb-3">
            <label for="code" class="form-label">Code</label>
            <input type="text" class="form-control" id="code" name="code" value="{{ old('code') }}" required>
        </div>

        <div class="mb-3">
            <label for="name" class="form-label">Intitulé du courrier</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description">{{ old('description') }}</textarea>
        </div>

        <div class="mb-3">
            <label for="date" class="form-label">Date</label>
            <input type="date" class="form-control" id="date" name="date" value="{{ old('date') }}" required>
        </div>

        {{-- Champ de sélection avec suggestions d'auteurs --}}
        <div class="mb-3">
            <label for="authorInput" class="form-label">Producteur</label>
            <input type="text" id="authorInput" class="form-control" autocomplete="off" />
            <div id="suggestions" class="list-group"></div>
            <input type="hidden" name="author_id" id="selectedAuthorId">
        </div>

        {{-- Champs de sélection avec options --}}
        <div class="mb-3">
            <label for="mail_priority_id" class="form-label">Priorité</label>
            <select class="form-select" id="mail_priority_id" name="mail_priority_id" required>
                @foreach ($priorities as $priority)
                    <option value="{{ $priority->id }}" {{ old('mail_priority_id') == $priority->id ? 'selected' : '' }}>
                        {{ $priority->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="mail_type_id" class="form-label">Type de courrier</label>
            <select class="form-select" id="mail_type_id" name="mail_type_id" required>
                @foreach ($types as $type)
                    <option value="{{ $type->id }}" {{ old('mail_type_id') == $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="mail_typology_id" class="form-label">Typologie</label>
            <select class="form-select" id="mail_typology_id" name="mail_typology_id" required>
                @foreach ($typologies as $typology)
                    <option value="{{ $typology->id }}" {{ old('mail_typology_id') == $typology->id ? 'selected' : '' }}>
                        {{ $typology->name }}
                    </option>
                @endforeach
            </select>
        </div>


        <div class="mb-3">
            <label for="document_type_id" class="form-label">Nature</label>
            <select class="form-select" id="document_type_id" name="document_type_id" required>
                @foreach ($documentTypes as $documentType)
                    <option value="{{ $documentType->id }}" {{ old('document_type_id') == $documentType->id ? 'selected' : '' }}>
                        {{ $documentType->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Bouton de soumission --}}
        <button type="submit" class="btn btn-primary">Créer</button>
    </form>
</div>

<script>
    const authorInput = document.getElementById('authorInput');
    const suggestionsContainer = document.getElementById('suggestions');
    const selectedAuthorIdInput = document.getElementById('selectedAuthorId');

    const authors = @json($authors);

    authorInput.addEventListener('input', () => {
        const searchTerm = authorInput.value.toLowerCase();
        const filteredAuthors = authors.filter(author =>
            author.name.toLowerCase().includes(searchTerm)
        );

        suggestionsContainer.innerHTML = '';

        filteredAuthors.forEach(author => {
            const suggestionItem = document.createElement('div');
            suggestionItem.classList.add('list-group-item', 'list-group-item-action');
            suggestionItem.textContent = author.name;

            suggestionItem.addEventListener('click', () => {
                authorInput.value = author.name;
                selectedAuthorIdInput.value = author.id;
                suggestionsContainer.innerHTML = '';
            });

            suggestionsContainer.appendChild(suggestionItem);
        });
    });
    </script>


@endsection
