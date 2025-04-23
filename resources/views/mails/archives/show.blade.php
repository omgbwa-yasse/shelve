@extends('layouts.app')
@section('content')

<div class="card">
    <div class="card-header">
        Mail Archiving #{{ $mailArchive->id }}
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label for="container_id" class="form-label">Container</label>
            <input type="text" class="form-control" id="container_id" value="{{ $mailArchive->container->name }}" readonly>
        </div>
        <div class="mb-3">
            <label for="mail_id" class="form-label">Mail</label>
            <input type="text" class="form-control" id="mail_id" value="{{ $mailArchive->mail->name }}" readonly>
        </div>
        <div class="mb-3">
            <label for="document_type_id" class="form-label">Document Type</label>
            <input type="text" class="form-control" id="document_type_id" value="{{ $mailArchive->document_type }}" readonly>
        </div>
        <div class="mb-3">
            <label for="created_at" class="form-label">Created At</label>
            <input type="text" class="form-control" id="created_at" value="{{ $mailArchive->created_at }}" readonly>
        </div>
        <div class="mb-3">
            <label for="updated_at" class="form-label">Updated At</label>
            <input type="text" class="form-control" id="updated_at" value="{{ $mailArchive->updated_at }}" readonly>
        </div>
    </div>
</div>

@endsection
