
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
                        <i class="bi bi-file-earmark-pdf fs-1 me-1"></i>
                        <div>
                            <p class="mb-1">
                                {{ $attachment->name ?? '' }} ({{ $attachment->size ?? '' }} KB) Ajouté par: {{ $attachment->creator->name ?? '' }}
                                <form action="{{ route('slip-record-show', )}}?r_id={{$slipRecord->id }}&a_id={{ $attachment->id }}" method="post">
                                    @csrf
                                    <button type="submit"  class="btn btn-primary me-2">Consulter</button>
                                </form>
                                </p>
                        </div>
                    </div>
                @endforeach
            </div>
            <h2 class="mb-4">Ajouter des fichiers</h2>
            <form action="{{ route('slip-record-upload') }}?s_id={{$slip->id}}&r_id={{ $slipRecord->id }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="file" class="mt-2 mt-3">
                <button type="submit" class="btn btn-danger mt-3">Enregistrer</button>
            </form>
            <div class="file-list" id="file-list"></div>
        </div>

        <script>
            // Fonction pour ajouter un fichier à la liste avec les boutons "Modifier" et "Supprimer"
            function addFileToList(file) {
                const fileList = document.getElementById('file-list');
                const fileItem = document.createElement('div');
                fileItem.classList.add('file-item');

                fileItem.innerHTML = `
                    <span>${file.name}</span>
                    <div>
                        <button class="btn btn-primary btn-sm mr-2" onclick="editFile('${file.id}')">Modifier</button>
                        <button class="btn btn-danger btn-sm" onclick="deleteFile('${file.id}', this)">Supprimer</button>
                    </div>
                `;

                fileList.appendChild(fileItem);
            }

            // Fonction pour supprimer un fichier
            function deleteFile(fileId, btn) {
                fetch(`/slipRecordAttachment/delete/${fileId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        btn.closest('.file-item').remove();
                    }
                });
            }

            // Fonction pour modifier un fichier (à implémenter)
            function editFile(fileId) {
                // Implement the edit functionality here
                alert('Fonction de modification à implémenter');
            }
        </script>
    </div>
@endsection
