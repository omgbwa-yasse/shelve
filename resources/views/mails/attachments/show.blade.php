@extends('layouts.app')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
</script>

@section('content')
    <div class="container">
        <h1>Attachment Details</h1>
{{--        {{ Storage::url($attachment->path) }}--}}
{{--        {{storage_path('app/' . $attachment->path)}}--}}
{{--        {{ storage_path('app/' . $attachment->path) }}--}}
        <table class="table">
            <tbody>
            <tr>
                <th>Name</th>
                <td>{{ $attachment->name }}</td>
            </tr>
            <tr>
                <th>Path</th>
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
            <a href="{{ route('attachments.download', $attachment->id) }}" class="btn btn-primary">Download</a>
        </div>
        <a href="{{ route('mail-attachment.index', $mail) }}" class="btn btn-secondary">Back</a>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            {{--const url = '{{ asset("storage/attachments/" . basename($attachment->path)) }}';--}}
            const url = '{{ storage_path('app/' . $attachment->path) }}';



            console.log('PDF URL:', url);
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
            const loadingTask = pdfjsLib.getDocument(url);
            let pdf = null;
            let currentPage = 1;

            loadingTask.promise.then(function(pdfDoc) {
                pdf = pdfDoc;
                console.log('PDF loaded');
                renderPage(currentPage);
            }).catch(function(error) {
                console.error('Error loading PDF:', error);
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
                }).catch(function(error) {
                    console.error('Error rendering page:', error);
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
