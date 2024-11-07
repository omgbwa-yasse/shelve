@extends('layouts.app')

@section('content')

<form action="{{ route('mail-archive.update', $mailArchiving->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="mb-3">
        <label for="container_id" class="form-label">Container</label>
        <select class="form-select" name="container_id" id="container_id">
            @foreach ($mailContainers as $container)
                <option value="{{ $container->id }}" {{ $container->id == $mailArchiving->container_id ? 'selected' : '' }}>{{ $container->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <label for="mail_id" class="form-label">Mail</label>
        <select class="form-select" name="mail_id" id="mail_id">
            @foreach ($mails as $mail)
                <option value="{{ $mail->id }}" {{ $mail->id == $mailArchiving->mail_id ? 'selected' : '' }}>{{ $mail->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <label for="document_type_id" class="form-label">Document Type</label>
        <select class="form-select" name="document_type_id" id="document_type_id">
            @foreach ($documentTypes as $documentType)
                <option value="{{ $documentType->id }}" {{ $documentType->id == $mailArchiving->document_type_id ? 'selected' : '' }}>{{ $documentType->name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Update</button>
</form>


@endsection
