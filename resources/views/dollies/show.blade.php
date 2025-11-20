@extends('layouts.app')
<style>
    .action-card {
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .action-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }
    .action-icon {
        font-size: 2rem;
        margin-bottom: 1rem;
    }
    .btn-action {
        width: 100%;
        border-radius: 0 0 0.25rem 0.25rem;
    }
</style>
@section('content')
    <div class="container">
        <div class="card mb-4">
            <div class="card-body">
                <h1 class="card-title">{{ $dolly->name }}</h1>
                <p class="card-text">{{ $dolly->description }}</p>
                <p class="card-text">
                    Type : <span class="badge bg-secondary"> {{ $dolly->category }}</span>
                </p>
                <div class="mt-3">
                    <a href="{{ route('dolly.edit', $dolly) }}" class="btn btn-warning">Modifier</a>
                    <form action="{{ route('dolly.destroy', $dolly) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce chariot ?')">Supprimer</button>
                    </form>

                    <button class="btn btn-secondary btnCleanDolly"
                            onclick="cleanDolly({{ $dolly->id }}, '{{ $dolly->category ?? '' }}')">
                        <i class="fas fa-trash-alt"></i> Vider le chariot
                    </button>

                    @if(in_array($dolly->category, ['digital_folder', 'digital_document']))
                        <a href="{{ route('dollies.action') }}?categ={{ $dolly->category }}&action=export_seda&id={{ $dolly->id }}" class="btn btn-primary">
                            <i class="bi bi-file-earmark-code"></i> Exporter SEDA
                        </a>
                    @endif

                    @if(in_array($dolly->category, ['digital_folder', 'digital_document', 'artifact', 'book', 'book_series']))
                        <a href="{{ route('dollies.action') }}?categ={{ $dolly->category }}&action=export_inventory&id={{ $dolly->id }}" class="btn btn-info">
                            <i class="bi bi-file-earmark-pdf"></i> Extraire Inventaire PDF
                        </a>
                    @endif

                    @if(in_array($dolly->category, ['book', 'book_series']))
                        <a href="{{ route('dollies.action') }}?categ={{ $dolly->category }}&action=export_isbd&id={{ $dolly->id }}" class="btn btn-success">
                            <i class="bi bi-file-earmark-text"></i> Exporter ISBD
                        </a>
                        <a href="{{ route('dollies.action') }}?categ={{ $dolly->category }}&action=export_marc&id={{ $dolly->id }}" class="btn btn-success">
                            <i class="bi bi-file-earmark-binary"></i> Exporter MARC
                        </a>
                        <a href="{{ route('dollies.action') }}?categ={{ $dolly->category }}&action=import_isbd&id={{ $dolly->id }}" class="btn btn-warning">
                            <i class="bi bi-file-earmark-arrow-down"></i> Importer ISBD
                        </a>
                        <a href="{{ route('dollies.action') }}?categ={{ $dolly->category }}&action=import_marc&id={{ $dolly->id }}" class="btn btn-warning">
                            <i class="bi bi-file-earmark-arrow-down-fill"></i> Importer MARC
                        </a>
                    @endif

                </div>
            </div>
        </div>

        <h2 class="mb-4">Actions disponibles</h2>
      @if(isset($dolly->category))
            @include("dollies.partials.{$dolly->category}")
      @endif



        <h2 class="mt-5 mb-4">Contenu du chariot</h2>
        @if($dolly->category === 'record' && $dolly->records->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-white">
                    <tr >
                        <th>ID</th>

                        <th>Dates</th>
                        <th>Niveau</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($dolly->records as $record)
                        <tr>
                            <td>{{ $record->id }}</td>
                            <td>{{ $record->date_start }} - {{ $record->date_end }}</td>
                            <td>{{ $record->level->name ?? 'N/A'}}</td>
                            <td>
                                <a href="{{ route('records.show', $record) }}" class="btn btn-sm btn-info">Voir</a>
                                <a href="{{ route('records.edit', $record) }}" class="btn btn-sm btn-warning">Modifier</a>
                                <form action="{{ route('dolly.remove-record', [$dolly, $record]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir retirer cet enregistrement du chariot ?')">Retirer</button>
                                </form>

                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        @elseif($dolly->category === 'mail' && $dolly->mails->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-white bg-dark">
                    <tr>
                        <th>ID</th>
                        <th>Titre</th>
                        <th>Date</th>
                        <th>Priorité</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($dolly->mails as $mail)
                        <tr>
                            <td>{{ $mail->id }}</td>
                            <td>{{ $mail->name }}</td>
                            <td>{{ $mail->date }}</td>
                            <td>{{ $mail->priority->name ?? 'N/A'}}</td>
                            <td>
                                @if($mail->senderOrganisation->id == Auth()->currentOrganisationId())
                                    <a href="{{ route('mail-received.show', $mail) }}" class="btn btn-sm btn-info">Voir</a>
                                    <a href="{{ route('mail-received.edit', $mail) }}" class="btn btn-sm btn-warning">Modifier</a>
                                    <button type="button" class="btn btn-sm btn-danger" id="item-mail-{{ $mail->id }}"
                                        onclick="removeItemFromDolly({{ $dolly->id }}, {{ $mail->id }}, 'mail')">
                                        Retirer
                                    </button>

                                @elseif($mail->recipientOrganisation->id == Auth()->currentOrganisationId())
                                    <a href="{{ route('mail-send.show', $mail) }}" class="btn btn-sm btn-info">Voir </a>
                                    <a href="{{ route('mail-send.edit', $mail) }}" class="btn btn-sm btn-warning">Modifier</a>
                                    <button type="button" class="btn btn-sm btn-danger" id="item-mail-{{ $mail->id }}"
                                            onclick="removeItemFromDolly({{ $dolly->id }}, {{ $mail->id }}, 'mail')">
                                        Retirer
                                    </button>
                                @endif

                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        @elseif($dolly->category === 'digital_folder' && $dolly->digitalFolders->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-white">
                    <tr>
                        <th>Code</th>
                        <th>Nom</th>
                        <th>Date de création</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($dolly->digitalFolders as $folder)
                        <tr>
                            <td>{{ $folder->code }}</td>
                            <td>{{ $folder->name }}</td>
                            <td>{{ $folder->created_at->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('record-digital-folders.show', $folder) }}" class="btn btn-sm btn-info">Voir</a>
                                <form action="{{ route('dolly.remove-digital-folder', [$dolly, $folder]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Retirer ce dossier du chariot ?')">Retirer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        @elseif($dolly->category === 'digital_document' && $dolly->digitalDocuments->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-white">
                    <tr>
                        <th>Code</th>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($dolly->digitalDocuments as $document)
                        <tr>
                            <td>{{ $document->code }}</td>
                            <td>{{ $document->name }}</td>
                            <td>{{ $document->type ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('record-digital-documents.show', $document) }}" class="btn btn-sm btn-info">Voir</a>
                                <form action="{{ route('dolly.remove-digital-document', [$dolly, $document]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Retirer ce document du chariot ?')">Retirer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        @elseif($dolly->category === 'artifact' && $dolly->artifacts->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-white">
                    <tr>
                        <th>Code</th>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($dolly->artifacts as $artifact)
                        <tr>
                            <td>{{ $artifact->code }}</td>
                            <td>{{ $artifact->name }}</td>
                            <td>{{ $artifact->type ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('record-artifacts.show', $artifact) }}" class="btn btn-sm btn-info">Voir</a>
                                <form action="{{ route('dolly.remove-artifact', [$dolly, $artifact]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Retirer cet artefact du chariot ?')">Retirer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        @elseif($dolly->category === 'book' && $dolly->books->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-white">
                    <tr>
                        <th>ISBN</th>
                        <th>Titre</th>
                        <th>Auteur</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($dolly->books as $book)
                        <tr>
                            <td>{{ $book->isbn }}</td>
                            <td>{{ $book->title }}</td>
                            <td>{{ $book->author ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('record-books.show', $book) }}" class="btn btn-sm btn-info">Voir</a>
                                <form action="{{ route('dolly.remove-book', [$dolly, $book]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Retirer ce livre du chariot ?')">Retirer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        @elseif($dolly->category === 'book_series' && $dolly->bookSeries->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-white">
                    <tr>
                        <th>Éditeur</th>
                        <th>Nom de la série</th>
                        <th>Nombre de livres</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($dolly->bookSeries as $series)
                        <tr>
                            <td>{{ $series->publisher->name ?? 'N/A' }}</td>
                            <td>{{ $series->name }}</td>
                            <td>{{ $series->books_count ?? 0 }}</td>
                            <td>
                                <a href="{{ route('record-book-series.show', $series) }}" class="btn btn-sm btn-info">Voir</a>
                                <form action="{{ route('dolly.remove-book-series', [$dolly, $series]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Retirer cette série du chariot ?')">Retirer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        @elseif($dolly->category === 'communication' && $dolly->communications->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-white">
                    <tr>
                        <th>Titre</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($dolly->communications as $communication)
                        <tr>
                            <td>{{ $communication->title }}</td>
                            <td>{{ $communication->date ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('communications.show', $communication) }}" class="btn btn-sm btn-info">Voir</a>
                                <form action="{{ route('dolly.remove-communication', [$dolly, $communication]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Retirer cette communication du chariot ?')">Retirer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        @elseif($dolly->category === 'room' && $dolly->rooms->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-white">
                    <tr>
                        <th>Nom</th>
                        <th>Code</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($dolly->rooms as $room)
                        <tr>
                            <td>{{ $room->name }}</td>
                            <td>{{ $room->code ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('rooms.show', $room) }}" class="btn btn-sm btn-info">Voir</a>
                                <form action="{{ route('dolly.remove-room', [$dolly, $room]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Retirer cette salle du chariot ?')">Retirer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        @elseif($dolly->category === 'container' && $dolly->containers->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-white">
                    <tr>
                        <th>Nom</th>
                        <th>Code</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($dolly->containers as $container)
                        <tr>
                            <td>{{ $container->name }}</td>
                            <td>{{ $container->code ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('containers.show', $container) }}" class="btn btn-sm btn-info">Voir</a>
                                <form action="{{ route('dolly.remove-container', [$dolly, $container]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Retirer cette boîte du chariot ?')">Retirer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        @elseif($dolly->category === 'shelf' && $dolly->shelve->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-white">
                    <tr>
                        <th>Nom</th>
                        <th>Code</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($dolly->shelve as $shelf)
                        <tr>
                            <td>{{ $shelf->name }}</td>
                            <td>{{ $shelf->code ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('shelves.show', $shelf) }}" class="btn btn-sm btn-info">Voir</a>
                                <form action="{{ route('dolly.remove-shelve', [$dolly, $shelf]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Retirer cette étagère du chariot ?')">Retirer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        @elseif($dolly->category === 'slip_record' && $dolly->slipRecords->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-white">
                    <tr>
                        <th>Code</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($dolly->slipRecords as $slipRecord)
                        <tr>
                            <td>{{ $slipRecord->code }}</td>
                            <td>{{ $slipRecord->date ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('slip-records.show', $slipRecord) }}" class="btn btn-sm btn-info">Voir</a>
                                <form action="{{ route('dolly.remove-slip-record', [$dolly, $slipRecord]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Retirer ce versement du chariot ?')">Retirer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        @else
            <div class="alert alert-info">Ce chariot est vide.</div>
        @endif
    </div>


    <script>

        function cleanDolly(dollyId, category) {
            if (confirm('Êtes-vous sûr de vouloir vider le chariot ?')) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

                const data = {
                    dolly_id: dollyId,
                    category: category
                };

                fetch('/dolly-handler/clean', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur lors du nettoyage');
                    }
                    return response.json();
                })
                .then(data => {
                    alert(data.message);
                    location.reload();
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Une erreur s\'est produite lors du nettoyage du chariot');
                });
            }
        }





        // Fonction pour retirer un élément du chariot
        function removeItemFromDolly(dollyId, itemId, category) {
            if (confirm('Êtes-vous sûr de vouloir retirer cet élément du chariot ?')) {

                const data = {
                    dolly_id: dollyId,
                    category : category,
                    items: [itemId]
                };

                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

                fetch('/dolly-handler/remove-items', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur lors de la suppression');
                    }
                    return response.json();
                })
                .then(data => {

                    document.getElementById(`item-${category}-${itemId}`).remove();
                    alert(data.message);
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Une erreur s\'est produite lors de la suppression de l\'élément');
                });
            }
        }

    </script>


@endsection
