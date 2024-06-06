@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Créer un courrier</h1>
        <form action="{{ route('mail.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="code" class="form-label">Code</label>
                <input type="text" class="form-control" id="code" name="code" required>
            </div>
            <div class="mb-3">
                <label for="object" class="form-label">Object</label>
                <input type="text" class="form-control" id="object" name="object" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description"></textarea>
            </div>
            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" required>
            </div>
            <div class="mb-3">
                <label for="author_id" class="form-label">Producteurs </label>
                <select class="form-select" id="author_id" name="author_id[]" required>
                    @foreach ($authors as $author)
                        <option value="{{ $author->id }}">{{ $author->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="mail_priority_id" class="form-label">Mail Priority</label>
                <select class="form-select" id="mail_priority_id" name="mail_priority_id" required>
                    @foreach ($priorities as $priority)
                        <option value="{{ $priority->id }}">{{ $priority->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="mail_type_id" class="form-label">Mail Type</label>
                <select class="form-select" id="mail_type_id" name="mail_type_id" required>
                    @foreach ($types as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="mail_typology_id" class="form-label">Mail Typology</label>
                <select class="form-select" id="mail_typology_id" name="mail_typology_id" required>
                    @foreach ($typologies as $typology)
                        <option value="{{ $typology->id }}">{{ $typology->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Create</button>
        </form>
    </div>
@endsection
