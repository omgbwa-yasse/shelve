@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="card-title mb-4">{{ $attachment->name }}</h1>
                <div class="mb-3">
                    <a href="{{ route('attachments.download', $attachment->id) }}" class="btn btn-primary me-2">
                        <i class="bi bi-download"></i> Download File
                    </a>
                    <a href="{{ route('mail-attachment.edit', [$mail, $attachment]) }}" class="btn btn-secondary me-2">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <form action="{{ route('mail-attachment.destroy', [$mail, $attachment]) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </form>
                </div>

                <div id="pdf-controls" class="mb-3 d-flex align-items-center" style="display: none;">
                    <button id="prev-page" class="btn btn-outline-primary me-2">
                        <i class="bi bi-chevron-left"></i> Previous
                    </button>
                    <div class="input-group" style="width: auto;">
                        <span class="input-group-text">Page</span>
                        <input type="number" id="page-number" class="form-control" min="1" style="width: 70px;">
                        <span class="input-group-text">of <span id="total-pages"></span></span>
                    </div>
                    <button id="next-page" class="btn btn-outline-primary ms-2">
                        Next <i class="bi bi-chevron-right"></i>
                    </button>
                </div>

                <div id="pdf-container" class="border rounded" style="height: 600px; overflow: auto; display: none;">
                    <iframe id="pdf-iframe" src="{{ route('mail-attachment.preview', $attachment->id) }}" style="width: 100%; height: 100%; border: none;"></iframe>
                </div>
                <div id="preview-unavailable" class="alert alert-warning" role="alert" style="display: none;">
                    Aperçu non disponible
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filePath = '{{ $attachment->path }}';
            const fileExtension = filePath.split('.').pop().toLowerCase();

            if (fileExtension !== 'pdf') {
                document.getElementById('preview-unavailable').style.display = 'block';
                return;
            }

            const container = document.getElementById('pdf-container');
            const pdfControls = document.getElementById('pdf-controls');

            container.style.display = 'block';
            pdfControls.style.display = 'flex';
        });
    </script>
@endsection
