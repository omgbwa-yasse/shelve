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
                    Type :
                    <span class="badge bg-primary">
                    @switch($dolly->type->name??'mail')
                            @case('record')
                                Archives
                                @break
                            @case('mail')
                                Courrier
                                @break
                            @case('communication')
                                Communication des archives
                                @break
                            @case('room')
                                Salle d'archives
                                @break
                            @case('container')
                                Boites d'archives et chronos
                                @break
                            @case('shelve')
                                Etagère
                                @break
                            @case('slip_record')
                                Archives (versement)
                                @break
                        @endswitch
                </span>
                </p>
                <div class="mt-3">
                    <a href="{{ route('dolly.edit', $dolly) }}" class="btn btn-warning">Modifier</a>
                    <form action="{{ route('dolly.destroy', $dolly) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce chariot ?')">Supprimer</button>
                    </form>
                    
                    <button class="btn btn-secondary btnCleanDolly" 
                            onclick="cleanDolly({{ $dolly->id }}, '{{ $type ?? ($dolly->type ? $dolly->type->name : '') }}')">
                        <i class="fas fa-print"></i> Vider le chariot
                    </button>

                </div>
            </div>
        </div>

        <h2 class="mb-4">Actions disponibles</h2>
      @if(isset($dolly->type->name))
            @include("dollies.partials.{$dolly->type->name}")
      @endif



        <h2 class="mt-5 mb-4">Contenu du chariot</h2>
        @if($dolly->type->name === 'record' && $dolly->records->isNotEmpty())
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
        @elseif($dolly->type->name === 'mail' && $dolly->mails->isNotEmpty())
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
        @else
            <div class="alert alert-info">Ce chariot est vide.</div>
        @endif

        <h2 class="mt-5 mb-4">Ajouter des éléments</h2>
        @switch($dolly->type->name)
            @case('record')
                <form action="{{ route('dolly.add-record', $dolly) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="record_id">Sélectionner un enregistrement</label>
                        <select class="form-control" id="record_id" name="record_id">
                            @foreach($records as $record)
                                <option value="{{ $record->id }}">{{ $record->code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </form>
                @break
            @case('mail')
                <form action="{{ route('dolly.add-mail', $dolly) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="mail_id">Sélectionner un courrier</label>
                        <select class="form-control" id="mail_id" name="mail_id">
                            @foreach($mails as $mail)
                                <option value="{{ $mail->id }}">{{ $mail->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </form>
                @break
            @case('communication')
                <form action="{{ route('dolly.add-communication', $dolly) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="communication_id">Sélectionner une communication</label>
                        <select class="form-control" id="communication_id" name="communication_id">
                            @foreach($communications as $communication)
                                <option value="{{ $communication->id }}">{{ $communication->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </form>
                @break
            @case('room')
                <form action="{{ route('dolly.add-room', $dolly) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="room_id">Sélectionner une salle</label>
                        <select class="form-control" id="room_id" name="room_id">
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}">{{ $room->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </form>
                @break
            @case('container')
                <form action="{{ route('dolly.add-container', $dolly) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="container_id">Sélectionner une boite</label>
                        <select class="form-control" id="container_id" name="container_id">
                            @foreach($containers as $container)
                                <option value="{{ $container->id }}">{{ $container->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </form>
                @break
            @case('shelve')
                <form action="{{ route('dolly.add-shelve', $dolly) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="shelve_id">Sélectionner une étagère</label>
                        <select class="form-control" id="shelve_id" name="shelve_id">
                            @foreach($shelves as $shelve)
                                <option value="{{ $shelve->id }}">{{ $shelve->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </form>
                @break
            @case('slip_record')
                <form action="{{ route('dolly.add-slip-record', $dolly) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="slip_record_id">Sélectionner un enregistrement de versement</label>
                        <select class="form-control" id="slip_record_id" name="slip_record_id">
                            @foreach($slip_records as $slip_record)
                                <option value="{{ $slip_record->id }}">{{ $slip_record->code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </form>
                @break
        @endswitch
    </div>


    <script>

        function cleanDolly(dollyId, type) {
            if (confirm('Êtes-vous sûr de vouloir vider le chariot ?')) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

                const data = {
                    dolly_id: dollyId,
                    type: type
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
        function removeItemFromDolly(dollyId, itemId, itemType) {
            if (confirm('Êtes-vous sûr de vouloir retirer cet élément du chariot ?')) {


                const data = {
                    dolly_id: dollyId,
                    type: itemType,
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

                    document.getElementById(`item-${itemType}-${itemId}`).remove();
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
