@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Mail</h1>
        <form action="{{ route('mails.update', $mail->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="code" class="form-label">Code</label>
                <input type="text" class="form-control" id="code" name="code" value="{{ $mail->code }}" required>
            </div>
            <div class="mb-3">
                <label for="object" class="form-label">Object</label>
                <input type="text" class="form-control" id="object" name="object" value="{{ $mail->object }}" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description">{{ $mail->description }}</textarea>
            </div>
            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" value="{{ $mail->date }}" required>
            </div>
            <div class="mb-3">
                <label for="mail_priority_id" class="form-label">Mail Priority</label>
                <select class="form-select" id="mail_priority_id" name="mail_priority_id" required>
                    @foreach ($mailPriorities as $priority)
                        <option value="{{ $priority->id }}" {{ $priority->id == $mail->mail_priority_id ? 'selected' : '' }}>
                            {{ $priority->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="mail_type_id" class="form-label">Mail Type</label>
                <select class="form-select" id="mail_type_id" name="mail_type_id" required>
                    @foreach ($mailTypes as $type)
                        <option value="{{ $type->id }}" {{ $type->id == $mail->mail_type_id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="mail_typology_id" class="form-label">Mail Typology</label>
                <select class="form-select" id="mail_typology_id" name="mail_typology_id" required>
                    @foreach ($mailTypologies as $typology)
                        <option value="{{ $typology->id }}" {{ $typology->id == $mail->mail_typology_id ? 'selected' : '' }}>
                            {{ $typology->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection
