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
                    <a href="{{  route('slips.records.show', [$slipRecord->slip, $slipRecord] ) }}" class="btn btn-primary me-2">
                        <i class= "bi bi-eye"></i> Retour
                    </a>
                </div>
                <div id="preview-container" class="border rounded" style="height: 600px; overflow: auto;">
                    <!-- Preview content will be inserted here -->
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
            const previewContainer = document.getElementById('preview-container');
            const previewUnavailable = document.getElementById('preview-unavailable');

            function showPreview(content) {
                previewContainer.innerHTML = content;
                previewContainer.style.display = 'block';
                previewUnavailable.style.display = 'none';
            }

            function showUnavailable() {
                previewContainer.style.display = 'none';
                previewUnavailable.style.display = 'block';
            }

            const previewUrl = '{{ route('mail-attachment.preview', $attachment->id) }}';

            switch (fileExtension) {
                case 'pdf':
                    showPreview(`<iframe src="${previewUrl}" style="width: 100%; height: 100%; border: none;"></iframe>`);
                    break;
                case 'jpg':
                case 'jpeg':
                case 'png':
                case 'gif':
                    showPreview(`<img src="${previewUrl}" alt="{{ $attachment->name }}" style="max-width: 100%; max-height: 100%; object-fit: contain;">`);
                    break;
                case 'mp4':
                case 'webm':
                case 'ogg':
                    showPreview(`
                        <video controls style="max-width: 100%; max-height: 100%;">
                            <source src="${previewUrl}" type="video/${fileExtension}">
                            Your browser does not support the video tag.
                        </video>
                    `);
                    break;
                default:
                    showUnavailable();
            }
        });
    </script>
@endsection
