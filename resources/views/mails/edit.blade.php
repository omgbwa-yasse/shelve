@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Mail</h1>

    <form action="{{ route('mails.update', $mail->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="reference">Reference</label>
            <input type="text" name="reference" class="form-control" required value="{{ $mail->reference }}">
        </div>

        <div class="form-group">
            <label for="object">Object</label>
            <input type="text" name="object" class="form-control" required maxlength="100" value="{{ $mail->object }}">
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" class="form-control" required>{{ $mail->description }}</textarea>
        </div>

        <div class="form-group">
            <label for="authors">Authors</label>
            <input type="text" name="authors" class="form-control" required maxlength="100" value="{{ $mail->authors }}">
        </div>

        <div class="form-group">
            <label for="mail_priority_id">Priority</label>
            <select name="mail_priority_id" class="form-control" required>
                @foreach($mailPriorities as $priority)
                    <option value="{{ $priority->id }}" {{ $mail->mail_priority_id == $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="mail_typology_id">Typology</label>
            <select name="mail_typology_id" class="form-control" required>
                @foreach($mailTypologies as $typology)
                    <option value="{{ $typology->iid }}" {{ $mail->mail_typology_id == $typology->iid ? 'selected' : '' }}>{{ $typology->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="document_id">Attachment</label>
            @if($mail->mailAttachment)
                <a href="{{ asset('storage/' . $mail->mailAttachment->path) }}" target="_blank">View current attachment</a>
            @endif
            <input type="file" name="document" class="form-control-file">
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
