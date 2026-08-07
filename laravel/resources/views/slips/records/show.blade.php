@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Détail de l'enregistrement</h1>
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title border-bottom pb-3">
                            Versement | <strong>{{ $slip->code ?? '' }} : {{ $slip->name }}</strong>
                        </h4>
                        <p class="lead">Description : {{ $slip->description }}</p>
                        <a class="btn btn-primary" href="{{ route('slips.show', $slip) }}">Consulter le bordereau</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Informations de l'enregistrement</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Code :</strong> {{ $slipRecord->code }}</p>
                                <p><strong>Intitulé :</strong> {{ $slipRecord->name }}</p>
                                <p><strong>Description :</strong> {{ $slipRecord->content }}</p>
                                <p><strong>Mots-clés :</strong>
                                    @if($slipRecord->keywords->isNotEmpty())
                                        @foreach($slipRecord->keywords as $keyword)
                                            <span class="badge bg-secondary me-1 keyword-badge"
                                                  style="cursor: pointer;"
                                                  onclick="filterByKeyword('{{ $keyword->name }}')"
                                                  title="Cliquez pour filtrer par ce mot-clé">
                                                {{ $keyword->name }}
                                            </span>
                                        @endforeach
                                    @else
                                        <em class="text-muted">Aucun mot-clé</em>
                                    @endif
                                </p>
                                <p><strong>Date :</strong>
                                    @if (is_null($slipRecord->date_exact) && is_null($slipRecord->date_end))
                                        {{ $slipRecord->date_start }}
                                    @elseif (is_null($slipRecord->date_exact) && !is_null($slipRecord->date_end))
                                        {{ $slipRecord->date_start }} - {{ $slipRecord->date_end }}
                                    @else
                                        {{ $slipRecord->date_exact }}
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Niveau de description :</strong> {{ $slipRecord->level->name }}</p>
                                <p><strong>Largeur :</strong> {{ $slipRecord->width }} cm, {{ $slipRecord->width_description }}</p>
                                <p><strong>Support :</strong> {{ $slipRecord->support->name }}</p>
                                <p><strong>Activité :</strong> {{ $slipRecord->activity->name }}</p>
                            </div>
                        </div>

                        <div class="">
                            @if(!$slip->is_received && !$slip->is_approved && !$slip->is_integrated)
                                <a href="{{ route('slips.index', $slip->id) }}" class="btn btn-secondary">Retour</a>
                                <a href="{{ route('slips.records.edit', [$slip, $slipRecord->id]) }}" class="btn btn-warning">Modifier</a>
                                <form action="{{ route('slips.records.destroy', [$slip->id, $slipRecord->id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet enregistrement ?')">Supprimer</button>
                                </form>
                            @elseif($slip->is_received && !$slip->is_approved && !$slip->is_integrated)
                                <a href="{{ route('slips.index', $slip->id) }}" class="btn btn-secondary">Retour</a>
                                <a href="{{ route('slips.records.edit', [$slip, $slipRecord->id]) }}" class="btn btn-warning">Modifier</a>
                                <form action="{{ route('slips.records.destroy', [$slip->id, $slipRecord->id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet enregistrement ?')">Supprimer</button>
                                </form>
                            @elseif($slip->is_received && $slip->is_approved && !$slip->is_integrated)
                                <a href="{{ route('slips.records.index', $slip->id) }}" class="btn btn-secondary">Retour</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-1">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Fichiers joints</h5>
                    </div>
                    <div class="card-body">
                        <div class="row" id="file-list">
                            @foreach ($slipRecord->attachments as $attachment)
                                <div class="col-md-4 col-lg-3 mb-4">
                                    <div class="card h-100 shadow-sm">
                                        <div class="card-img-top" style="height: 200px; overflow: hidden;">
                                            @if ($attachment->thumbnail_path)
                                                <img src="{{ asset('storage/' . $attachment->thumbnail_path) }}" alt="Thumbnail" class="img-fluid" style="width: 100%; object-fit: cover; object-position: top;">
                                            @else
                                                <div class="d-flex align-items-center justify-content-center h-100 bg-light">
                                                    <i class="bi bi-file-earmark-pdf fs-1 text-secondary"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="card-body p-3">
                                            <h6 class="card-title text-truncate mb-1" title="{{ $attachment->name ?? '' }}">{{ $attachment->name ?? '' }}</h6>
                                            <p class="card-text small text-muted mb-2">
                                                {{ $attachment->size ?? '' }} KB
                                            </p>
                                            <p class="card-text small text-muted mb-3">
                                                Ajouté par: {{ $attachment->creator->name ?? '' }}
                                            </p>
                                            <form action="{{ route('slip-record-show') }}?r_id={{$slipRecord->id }}&a_id={{ $attachment->id }}" method="post">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-primary btn-sm w-100">Consulter</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

{{--        <div class="row mt-1">--}}
{{--            <div class="col-md-12">--}}
{{--                <div class="card">--}}
{{--                    <div class="card-body">--}}
{{--                        <h5 class="card-title mb-4">Ajouter des fichiers</h5>--}}
{{--                        <form id="uploadForm" action="{{ route('slip-record-upload') }}?s_id={{$slip->id}}&r_id={{ $slipRecord->id }}" method="POST" enctype="multipart/form-data">--}}
{{--                            @csrf--}}
{{--                            <div class="mb-3">--}}
{{--                                <input type="file" name="file" id="fileInput" class="form-control" accept=".pdf,.jpg,.jpeg,.png">--}}
{{--                            </div>--}}
{{--                            <canvas id="pdfThumbnail" style="display:none;"></canvas>--}}
{{--                            <input type="hidden" name="thumbnail" id="thumbnailInput">--}}
{{--                            <button type="submit" class="btn btn-primary">Enregistrer</button>--}}
{{--                        </form>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}




        <div class="row mt-1">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Contenants associés</h5>
                        @if($slipRecord->containers->isNotEmpty())
                            <div class="row">
                                @foreach ($slipRecord->containers as $container)
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card border-left-primary h-100">
                                            <div class="card-body">
                                                <h6 class="card-title text-primary">{{ $container->code }}</h6>
                                                <p class="card-text small text-muted mb-1">
                                                    <strong>Description:</strong> {{ $container->description ?? 'Non renseignée' }}
                                                </p>
                                                <p class="card-text small text-muted mb-1">
                                                    <strong>Étagère:</strong>
                                                    @if($container->shelf)
                                                        {{ $container->shelf->code }}
                                                        @if($container->shelf->observation)
                                                            <small class="text-muted">({{ $container->shelf->observation }})</small>
                                                        @endif
                                                    @else
                                                        <span class="text-warning">Non assignée</span>
                                                        @if($container->shelve_id)
                                                            <small class="text-danger">(ID: {{ $container->shelve_id }} - Étagère introuvable)</small>
                                                        @endif
                                                    @endif
                                                </p>
                                                <p class="card-text small text-muted mb-0">
                                                    <strong>Statut:</strong>
                                                    <span class="badge bg-info">{{ $container->status->name ?? 'Non défini' }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Aucun contenant associé à cet enregistrement.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

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
                                console.log('Thumbnail generated:', thumbnailDataUrl.substring(0, 100) + '...');
                            });
                        });
                    });
                };
                fileReader.readAsArrayBuffer(pdfFile);
            }

            document.getElementById('uploadForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                console.log('Thumbnail data before send:', formData.get('thumbnail') ? 'Present' : 'Not present');

                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Server response:', data);
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

        <script>
        function filterByKeyword(keyword) {
            // Rediriger vers la page d'index des enregistrements avec le filtre de mot-clé
            window.location.href = '{{ route("slips.records.index", ["slip" => $slip->id]) }}?keyword=' + encodeURIComponent(keyword);
        }
        </script>


@endsection
