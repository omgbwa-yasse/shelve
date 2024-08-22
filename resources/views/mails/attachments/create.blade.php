@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Add Attachment to Mail #{{ $mail->id }}</h1>
        <form action="{{ route('mail-attachment.store', $mail->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="file" class="form-label">File</label>
                <input type="file" class="form-control" id="file" name="file" accept="application/pdf" required>
            </div>
            <div id="pdf-preview" class="mb-3"></div>
            <button type="submit" class="btn btn-primary">Add Attachment</button>
        </form>
    </div>

    <script src="https://mozilla.github.io/pdf.js/build/pdf.js"></script>
    <script>
        document.getElementById('file').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file && file.type === 'application/pdf') {
                const fileReader = new FileReader();
                fileReader.onload = function() {
                    const pdfData = new Uint8Array(this.result);
                    const loadingTask = pdfjsLib.getDocument({data: pdfData});
                    loadingTask.promise.then(function(pdf) {
                        pdf.getPage(1).then(function(page) {
                            const scale = 1.5;
                            const viewport = page.getViewport({scale: scale});
                            const canvas = document.createElement('canvas');
                            const context = canvas.getContext('2d');
                            canvas.height = viewport.height;
                            canvas.width = viewport.width;
                            const renderContext = {
                                canvasContext: context,
                                viewport: viewport
                            };
                            page.render(renderContext);
                            document.getElementById('pdf-preview').innerHTML = '';
                            document.getElementById('pdf-preview').appendChild(canvas);
                        });
                    });
                };
                fileReader.readAsArrayBuffer(file);
            } else {
                document.getElementById('pdf-preview').innerHTML = 'Please select a PDF file.';
            }
        });
    </script>
@endsection
