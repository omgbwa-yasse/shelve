@extends('layouts.app')

@section('content')
    <h1>Courrier Envoyer</h1>
    <form action="{{ route('mails.store', $mailTypeId) }}" method="POST" enctype="multipart/form-data">
        @csrf

            <div class="mb-3">
                <label for="code" class="form-label">Code:</label>
                <input type="text" name="code" id="code" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="object" class="form-label">Object:</label>
                <input type="text" name="object" id="object" class="form-control" maxlength="100" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description:</label>
                <textarea name="description" id="description" class="form-control"></textarea>
            </div>

            <div class="mb-3">
                <label for="organisation_id" class="form-label">Organisation :</label>
                <select name="organisation_id" id="organisation_id" class="form-select">
                    @foreach($organisations as $organisation)
                        <option value="{{ $organisation->id }}">{{ $organisation->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="author_id" class="form-label">Authors :</label>
                <select name="author_id" id="author_id" class="form-select">
                    @foreach($authors as $author)
                        <option value="{{ $author->id }}">{{ $author->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="mail_priority_id" class="form-label">Mail Priority:</label>
                <select name="mail_priority_id" id="mail_priority_id" class="form-select">
                    @foreach($mailPriorities as $mailPriority)
                        <option value="{{ $mailPriority->id }}">{{ $mailPriority->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="mail_typology_id" class="form-label">Mail Typology:</label>
                <select name="mail_typology_id" id="mail_typology_id" class="form-select">
                    @foreach($mailTypologies as $mailTypology)
                        <option value="{{ $mailTypology->id }}">{{ $mailTypology->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="document" class="form-label">Document:</label>
                <input type="file" name="document" id="document" class="form-control">
            </div>

            <div>
                <button type="submit" class="btn btn-primary">Create Mail</button>
            </div>
    </form>
@endsection
