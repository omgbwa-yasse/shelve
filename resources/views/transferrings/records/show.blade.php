
@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Détail de l'enregistrement</h1>
        <div class="col-md-12">
            <h4 class="border-bottom pb-3">
                Versement | <strong>{{ $slip->code ?? '' }} : {{ $slip->name }}</strong>
            </h4>
            <p class="lead">Description : {{ $slip->description }}</p>
            <a class="btn btn-primary mb-3" href="{{ route('slips.show', $slip) }}" role="button">Consulter le bordereau</a>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row mb-4">
                    <div>
                        <p><strong>Code</strong> : {{ $slipRecord->code }}</p>
                        <p class="card-title"><strong>Intitulé</strong> : {{ $slipRecord->name }}</p>
                        <p class="card-text"><strong>Description</strong> : {{ $slipRecord->content }}</p>
                    </div>
                </div>
                <div class="d-flex justify-content-between">
                    <p>
                        @if (is_null($slipRecord->date_exact) && is_null($slipRecord->date_end))
                            Date : {{ $slipRecord->date_start }}
                        @elseif (is_null($slipRecord->date_exact) && !is_null($slipRecord->date_end))
                            Dates extrêmes : {{ $slipRecord->date_start }} - {{ $slipRecord->date_end }}
                        @else
                            Date : {{ $slipRecord->date_exact }}
                        @endif
                    </p>
                    <p><strong>Niveau de description</strong> : {{ $slipRecord->level->name }}</p>
                    <p><strong>Width</strong> : {{ $slipRecord->width }} cm, {{ $slipRecord->width_description }}</p>
                    <p><strong>Support</strong> : {{ $slipRecord->support->name }}</p>
                    <p><strong>Activité</strong> : {{ $slipRecord->activity->name }}</p>
                    <p><strong>Boites/chrono</strong> : {{ $slipRecord->container->name }}</p>
                </div>

                @if(!$slip->is_received && !$slip->is_approved && !$slip->is_integrated)
                    <a href="{{ route('slips.index', $slip->id) }}" class="btn btn-secondary mt-3">Retour</a>
                    <a href="{{ route('slips.records.edit', [$slip, $slipRecord->id]) }}" class="btn btn-warning mt-3">Modifier</a>
                    <form action="{{ route('slips.records.destroy', [$slip->id, $slipRecord->id]) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger mt-3" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet enregistrement ?')">Supprimer</button>
                    </form>
                @elseif($slip->is_received && !$slip->is_approved && !$slip->is_integrated)
                    <a href="{{ route('slips.index', $slip->id) }}" class="btn btn-secondary mt-3">Retour</a>
                    <a href="{{ route('slips.records.edit', [$slip, $slipRecord->id]) }}" class="btn btn-warning mt-3">Modifier</a>
                    <form action="{{ route('slips.records.destroy', [$slip->id, $slipRecord->id]) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger mt-3" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet enregistrement ?')">Supprimer</button>
                    </form>
                @elseif($slip->is_received && $slip->is_approved && !$slip->is_integrated)
                    <a href="{{ route('slips.records.index', $slip->id) }}" class="btn btn-secondary mt-3">Retour</a>
                @endif
            </div>
        </div>
        <div class="container mt-5">
            <div class="file-list row" id="file-list">
                @foreach ($slipRecord->attachments as $attachment)
                    <div class="file-item col-md-6 col-lg-4 d-flex align-items-center">
                        @if ($attachment->thumbnail_path)
                            <img src="{{ asset('storage/' . $attachment->thumbnail_path) }}" alt="Thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                        @else
                            <i class="bi bi-file-earmark-pdf fs-1 me-1"></i>
                        @endif
                        <div>
                            <p class="mb-1">
                                {{ $attachment->name ?? '' }} ({{ $attachment->size ?? '' }} KB) Ajouté par: {{ $attachment->creator->name ?? '' }}
                            <form action="{{ route('slip-record-show') }}?r_id={{$slipRecord->id }}&a_id={{ $attachment->id }}" method="post">
                                @csrf
                                <button type="submit" class="btn btn-primary me-2">Consulter</button>
                            </form>
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
            <h2 class="mb-4">Ajouter des fichiers</h2>
            <form id="uploadForm" action="{{ route('slip-record-upload') }}?s_id={{$slip->id}}&r_id={{ $slipRecord->id }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="file" id="fileInput" class="mt-2 mt-3" accept=".pdf,.jpg,.jpeg,.png">
                <canvas id="pdfThumbnail" style="display:none;"></canvas>
                <input type="hidden" name="thumbnail" id="thumbnailInput">
                <button type="submit" class="btn btn-danger mt-3">Enregistrer</button>
            </form>
            <div class="file-list" id="file-list"></div>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.9.359/pdf.min.js"></script>
        <script>
            document.getElementById('fileInput').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file.type === 'application/pdf') {
                    generatePdfThumbnail(file);
                }
            });

            function generatePdfThumbnail(pdfFile) {
                const fileReader = new FileReader();
                fileReader.onload = function() {
                    const typedarray = new Uint8Array(this.result);

                    pdfjsLib.getDocument(typedarray).promise.then(function(pdf) {
                        pdf.getPage(1).then(function(page) {
                            const scale = 1.5;
                            const viewport = page.getViewport({ scale: scale });
                            const canvas = document.getElementById('pdfThumbnail');
                            const context = canvas.getContext('2d');
                            canvas.height = viewport.height;
                            canvas.width = viewport.width;

                            const renderContext = {
                                canvasContext: context,
                                viewport: viewport
                            };
                            page.render(renderContext).promise.then(() => {
                                const thumbnailDataUrl = canvas.toDataURL('image/jpeg');
                                document.getElementById('thumbnailInput').value = thumbnailDataUrl;
                                console.log('Thumbnail generated:', thumbnailDataUrl.substring(0, 100) + '...'); // Log pour déboguer
                            });
                        });
                    });
                };
                fileReader.readAsArrayBuffer(pdfFile);
            }

            document.getElementById('uploadForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                console.log('Thumbnail data before send:', formData.get('thumbnail') ? 'Present' : 'Not present'); // Log pour déboguer

                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Server response:', data); // Log pour déboguer
                        if (data.success) {
                            location.reload();
                        } else {
                            console.error('Upload failed:', data.message);
                            alert('L\'upload a échoué. Veuillez réessayer.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Une erreur est survenue. Veuillez réessayer.');
                    });
            });
        </script>
    </div>
@endsection
