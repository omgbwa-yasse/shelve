@extends('layouts.app')

@section('content')

<div class="container mt-4">
    <h2>Edit Author</h2>

    <form action="{{ route('mail-author.update', $author->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="type_id" class="form-label">Type</label>
            <select id="type_id" name="type_id" class="form-control" required>
                @foreach ($authorTypes as $type)
                    <option value="{{ $type->id }}" {{ $author->type_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" id="name" name="name" class="form-control" data-field="name" value="{{ $author->name }}" required>
        </div>

        <div class="mb-3">
            <label for="parallel_name" class="form-label">Parallel Name</label>
            <input type="text" id="parallel_name" name="parallel_name" class="form-control" data-field="parallel_name" value="{{ $author->parallel_name }}">
        </div>

        <div class="mb-3">
            <label for="other_name" class="form-label">Other Name</label>
            <input type="text" id="other_name" name="other_name" class="form-control" data-field="other_name" value="{{ $author->other_name }}">
        </div>

        <div class="mb-3">
            <label for="lifespan" class="form-label">Lifespan</label>
            <input type="text" id="lifespan" name="lifespan" class="form-control" value="{{ $author->lifespan }}">
        </div>

        <div class="mb-3">
            <label for="locations" class="form-label">Locations</label>
            <input type="text" id="locations" name="locations" class="form-control" data-field="locations" value="{{ $author->locations }}">
        </div>

        <div class="mb-3">
            <label for="parent_id" class="form-label">Parent Author</label>
            <select id="parent_id" name="parent_id" class="form-control">
                <option value="">None</option>
                @foreach ($parents as $parent)
                    <option value="{{ $parent->id }}" {{ $author->parent_id == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Update Author<button type="submit" class="btn btn-primary">Update Author</button>
    </form>
</div>

<script>
const authors = @json($authors);
const authorInputs = document.querySelectorAll('input[data-field]');
const suggestionsList = document.createElement('ul');
suggestionsList.classList.add('suggestions-list');

document.body.appendChild(suggestionsList); // Ou dans un conteneur plus spécifique

authorInputs.forEach(input => {
    input.addEventListener('input', () => {
        const searchTerm = input.value.toLowerCase();
        const field = input.dataset.field;

        const filteredAuthors = authors.filter(author =>
            author[field]?.toLowerCase().includes(searchTerm)
        );

        suggestionsList.innerHTML = '';

        const rect = input.getBoundingClientRect();
        suggestionsList.style.top = `${rect.bottom}px`;
        suggestionsList.style.left = `${rect.left}px`;

        filteredAuthors.forEach(author => {
            const suggestionItem = document.createElement('li');
            suggestionItem.textContent = author[field];
            suggestionItem.addEventListener('click', () => {
                input.value = author[field];
                suggestionsList.innerHTML = '';
            });
            suggestionsList.appendChild(suggestionItem);
        });
    });

    input.addEventListener('blur', () => {
        setTimeout(() => {
            suggestionsList.innerHTML = '';
        }, 200); // Délai pour permettre le clic sur une suggestion
    });
});
</script>


@endsection
