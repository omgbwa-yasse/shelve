@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="">
            <div class="">
                <h3 class="card-title mb-4">{{ $attachment->name }}</h3>
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
