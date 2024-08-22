@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Attachment Details</h1>
        <table class="table">
            <tbody>
            <tr>
                <th>Name</th>
                <td>{{ $attachment->name }}</td>
            </tr>
            <tr>
                <th>Path</th>{{ asset('app/public/' . $attachment->path) }}
                <td>{{ $attachment->path }}</td>
            </tr>
            <tr>
                <th>Crypt</th>
                <td>{{ $attachment->crypt }}</td>
            </tr>
            <tr>
                <th>Size</th>
                <td>{{ $attachment->size }}</td>
            </tr>
            <tr>
                <th>Created At</th>
                <td>{{ $attachment->created_at }}</td>
            </tr>
            <tr>
                <th>Updated At</th>
                <td>{{ $attachment->updated_at }}</td>
            </tr>
            </tbody>
        </table>
        <div id="pdf-container" style="width: 100%; height: 600px; border: 1px solid #ccc;">
            <canvas id="pdf-canvas"></canvas>
        </div>
        <div class="mt-3">
            <button id="prev-page" class="btn btn-secondary">Previous</button>
            <button id="next-page" class="btn btn-secondary">Next</button>
            <a href="{{ asset('app/public/' . $attachment->path) }}" class="btn btn-primary" download>Download</a>
        </div>
        <a href="{{ route('mail-attachment.index', $mail) }}" class="btn btn-secondary">Back</a>
    </div>

    <script src="https://mozilla.github.io/pdf.js/build/pdf.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const url = '{{ asset('storage/' . $attachment->path) }}';
            const loadingTask = pdfjsLib.getDocument(url);
            let pdf = null;
            let currentPage = 1;

            loadingTask.promise.then(function(pdfDoc) {
                pdf = pdfDoc;
                renderPage(currentPage);
            });

            function renderPage(pageNum) {
                pdf.getPage(pageNum).then(function(page) {
                    const scale = 1.5;
                    const viewport = page.getViewport({scale: scale});
                    const canvas = document.getElementById('pdf-canvas');
                    const context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    const renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };
                    page.render(renderContext);
                });
            }

            document.getElementById('prev-page').addEventListener('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    renderPage(currentPage);
                }
            });

            document.getElementById('next-page').addEventListener('click', function() {
                if (currentPage < pdf.numPages) {
                    currentPage++;
                    renderPage(currentPage);
                }
            });
        });
    </script>
@endsection
