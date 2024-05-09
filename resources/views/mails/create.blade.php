@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create Mail</h1>

    <form action="{{ route('mails.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label for="code">Code</label>
            <input type="text" name="code" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="object">Object</label>
            <input type="text" name="object" class="form-control" required maxlength="100">
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>

        <div class="form-group">
            <label for="authors">Authors</label>
            <input type="text" name="authors" class="form-control" required maxlength="100">
        </div>

        <div class="form-group">
            <label for="mail_priority_id">Priority</label>
            <select name="mail_priority_id" class="form-control" required>
                @foreach($mailPriorities as $priority)
                    <option value="{{ $priority->id }}">{{ $priority->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="mail_typology_id">Typology</label>
            <select name="mail_typology_id" class="form-control" required>
                @foreach($mailTypologies as $typology)
                    <option value="{{ $typology->id }}">{{ $typology->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="document">Attachment</label>
            <input type="file" name="document" class="form-control-file">
        </div>

        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</div>
@endsection
