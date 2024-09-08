@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ $dolly->name }}</h1>
    <p>{{ $dolly->description }}</p>

        <p>  Type : <strong>
            @if($dolly->type->name == 'record')
                Archives
            @elseif($dolly->type->name == 'mail')
                Courrier
            @elseif($dolly->type->name == 'communication')
                Communication des archives
            @elseif($dolly->type->name == 'room')
                Salle d'archives
            @elseif($dolly->type->name == 'container')
                Boites d'archives et chronos
            @elseif($dolly->type->name == 'shelve')
                Etagère
            @elseif($dolly->type->name == 'slip_record')
                Archives (versement)
            @endif
            </strong>
        </p>

    <a href="{{ route('dolly.edit', $dolly) }}" class="btn btn-warning">Edit</a>
    <form action="{{ route('dolly.destroy', $dolly) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this dolly?')">Delete</button>
    </form>




    {{-- Afficher un formulaire spécifique selon le type --}}
    @if($dolly->type->name == 'record')
    <ul class="list-group">
        <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
            <div class="d-flex align-items-left">
                <i class="bi bi-trash" style="margin-right: 10px;"></i>
                Vider le chariot
            </div>
            <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=clean&id={{ $dolly->id }}">
                <button class="btn btn-primary">Exécuter</button>
            </a>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
            <div class="d-flex align-items-left">
                <i class="bi bi-layers" style="margin-right: 10px;"></i>
                Changer le niveau de description
            </div>
            <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=level&id={{ $dolly->id }}">
                <button class="btn btn-primary">Exécuter</button>
            </a>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
            <div class="d-flex align-items-left">
                <i class="bi bi-shield-check" style="margin-right: 10px;"></i>
                Changer le status des descriptions
            </div>
            <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=status&id={{ $dolly->id }}">
                <button class="btn btn-primary">Exécuter</button>
            </a>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
            <div class="d-flex align-items-left">
                <i class="bi bi-archive" style="margin-right: 10px;"></i>
                Changer les boites/chronos d'archives
            </div>
            <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=container&id={{ $dolly->id }}">
                <button class="btn btn-primary">Exécuter</button>
            </a>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
            <div class="d-flex align-items-left">
                <i class="bi bi-diagram-3" style="margin-right: 10px;"></i>
                Changer la classe d'activité
            </div>
            <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=activity&id={{ $dolly->id }}">
                <button class="btn btn-primary">Exécuter</button>
            </a>
        </li>
       <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
            <div class="d-flex align-items-left">
                <i class="bi bi-calendar" style="margin-right: 10px;"></i>
                Changer de dates
            </div>
            <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=dates&id={{ $dolly->id }}">
                <button class="btn btn-primary">Exécuter</button>
            </a>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
            <div class="d-flex align-items-left">
                <i class="bi bi-file-earmark-arrow-down" style="margin-right: 10px;"></i>
                Exporter l'instrument de recherche
            </div>
            <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=export&id={{ $dolly->id }}">
                <button class="btn btn-primary">Exécuter</button>
            </a>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
            <div class="d-flex align-items-left">
                <i class="bi bi-printer" style="margin-right: 10px;"></i>
                Imprimer l'instrument de recherche
            </div>
            <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=print&id={{ $dolly->id }}">
                <button class="btn btn-primary">Exécuter</button>
            </a>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
            <div class="d-flex align-items-left">
                <i class="bi bi-x-circle" style="margin-right: 10px;"></i>
                Supprimer de la base
            </div>
            <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=delete&id={{ $dolly->id }}">
                <button class="btn btn-primary">Exécuter</button>
            </a>
        </li>


    </ul>






    @elseif($dolly->type->name == 'mail')

    <ul class="list-group">
        <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
            <div class="d-flex align-items-left">
                <i class="bi bi-trash" style="margin-right: 10px;"></i>
                Vider le chariot
            </div>
            <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=clean&id={{ $dolly->id }}">
                <button class="btn btn-primary">Exécuter</button>
            </a>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
            <div class="d-flex align-items-left">
                <i class="bi bi-calendar" style="margin-right: 10px;"></i>
                Changer les dates des courriers
            </div>
            <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=dates&id={{ $dolly->id }}">
                <button class="btn btn-primary">Exécuter</button>
            </a>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
            <div class="d-flex align-items-left">
                <i class="bi bi-exclamation-circle" style="margin-right: 10px;"></i>
                Changer la priorité des courriers
            </div>
            <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=priority&id={{ $dolly->id }}">
                <button class="btn btn-primary">Exécuter</button>
            </a>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
            <div class="d-flex align-items-left">
                <i class="bi bi-archive" style="margin-right: 10px;"></i>
                Archiver les courriers
            </div>
            <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=archive&id={{ $dolly->id }}">
                <button class="btn btn-primary">Exécuter</button>
            </a>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
            <div class="d-flex align-items-left">
                <i class="bi bi-file-earmark-arrow-down" style="margin-right: 10px;"></i>
                Exporter la liste du courrier
            </div>
            <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=export&id={{ $dolly->id }}">
                <button class="btn btn-primary">Exécuter</button>
            </a>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
            <div class="d-flex align-items-left">
                <i class="bi bi-printer" style="margin-right: 10px;"></i>
                Imprimer la liste de courrier
            </div>
            <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=print&id={{ $dolly->id }}">
                <button class="btn btn-primary">Exécuter</button>
            </a>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
            <div class="d-flex align-items-left">
                <i class="bi bi-x-circle" style="margin-right: 10px;"></i>
                Supprimer de la base
            </div>
            <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=delete&id={{ $dolly->id }}">
                <button class="btn btn-primary">Exécuter</button>
            </a>
        </li>
    </ul>







    @elseif($dolly->type->name == 'shelf')
        <ul class="list-group">
            <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
                <div class="d-flex align-items-left">
                    <i class="bi bi-trash" style="margin-right: 10px;"></i>
                    Vider le chariot
                </div>
                <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=clean&id={{ $dolly->id }}">
                    <button class="btn btn-primary">Exécuter</button>
                </a>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
                <div class="d-flex align-items-left">
                    <i class="bi bi-house-door" style="margin-right: 10px;"></i>
                    Changer de salle d'archives
                </div>
                <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=room&id={{ $dolly->id }}">
                    <button class="btn btn-primary">Exécuter</button>
                </a>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
                <div class="d-flex align-items-left">
                    <i class="bi bi-x-circle" style="margin-right: 10px;"></i>
                    Supprimer de la base
                </div>
                <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=delete&id={{ $dolly->id }}">
                    <button class="btn btn-primary">Exécuter</button>
                </a>
            </li>
        </ul>








    @elseif($dolly->type->name == 'container')
        <ul class="list-group">
            <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
                <div class="d-flex align-items-left">
                    <i class="bi bi-trash" style="margin-right: 10px;"></i>
                    Vider le chariot
                </div>
                <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=clean&id={{ $dolly->id }}">
                    <button class="btn btn-primary">Exécuter</button>
                </a>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
                <div class="d-flex align-items-left">
                    <i class="bi bi-layout-text-sidebar-reverse" style="margin-right: 10px;"></i>
                    Changer d'étagère
                </div>
                <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=shelf&id={{ $dolly->id }}">
                    <button class="btn btn-primary">Exécuter</button>
                </a>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
                <div class="d-flex align-items-left">
                    <i class="bi bi-x-circle" style="margin-right: 10px;"></i>
                    Supprimer de la base
                </div>
                <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=delete&id={{ $dolly->id }}">
                    <button class="btn btn-primary">Exécuter</button>
                </a>
            </li>
        </ul>






    @elseif($dolly->type->name == 'communication')
        <ul class="list-group">
            <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
                <div class="d-flex align-items-left">
                    <i class="bi bi-trash" style="margin-right: 10px;"></i>
                    Vider le chariot
                </div>
                <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=clean&id={{ $dolly->id }}">
                    <button class="btn btn-primary">Exécuter</button>
                </a>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
                <div class="d-flex align-items-left">
                    <i class="bi bi-calendar-date" style="margin-right: 10px;"></i>
                    Changer la date de retour
                </div>
                <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=return_date&id={{ $dolly->id }}">
                    <button class="btn btn-primary">Exécuter</button>
                </a>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
                <div class="d-flex align-items-left">
                    <i class="bi bi-calendar-check" style="margin-right: 10px;"></i>
                    Changer la date effective de retour
                </div>
                <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=return_date_effective&id={{ $dolly->id }}">
                    <button class="btn btn-primary">Exécuter</button>
                </a>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
                <div class="d-flex align-items-left">
                    <i class="bi bi-shuffle" style="margin-right: 10px;"></i>
                    Changer le status de communication
                </div>
                <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=status&id={{ $dolly->id }}">
                    <button class="btn btn-primary">Exécuter</button>
                </a>
            </li>

            <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
                <div class="d-flex align-items-left">
                    <i class="bi bi-x-circle" style="margin-right: 10px;"></i>
                    Supprimer de la base
                </div>
                <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=delete&id={{ $dolly->id }}">
                    <button class="btn btn-primary">Exécuter</button>
                </a>
            </li>
        </ul>





    @elseif($dolly->type->name == 'room')
    <ul class="list-group">
        <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
            <div class="d-flex align-items-left">
                <i class="bi bi-trash" style="margin-right: 10px;"></i>
                Vider le chariot
            </div>
            <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=clean&id={{ $dolly->id }}">
                <button class="btn btn-primary">Exécuter</button>
            </a>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
            <div class="d-flex align-items-left">
                <i class="bi bi-building" style="margin-right: 10px;"></i>
                Changer de bâtiment de la salle d'archives
            </div>
            <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=floor&id={{ $dolly->id }}">
                <button class="btn btn-primary">Exécuter</button>
            </a>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
            <div class="d-flex align-items-left">
                <i class="bi bi-x-circle" style="margin-right: 10px;"></i>
                Supprimer de la base
            </div>
            <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=delete&id={{ $dolly->id }}">
                <button class="btn btn-primary">Exécuter</button>
            </a>
        </li>
    </ul>




    @elseif($dolly->type->name == 'shelf')
        <ul class="list-group">
            <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
                <div class="d-flex align-items-left">
                    <i class="bi bi-trash" style="margin-right: 10px;"></i>
                    Vider le chariot
                </div>
                <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=clean&id={{ $dolly->id }}">
                    <button class="btn btn-primary">Exécuter</button>
                </a>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
                <div class="d-flex align-items-left">
                    <i class="bi bi-archive" style="margin-right: 10px;"></i>
                    Changer de salle d'archives
                </div>
                <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=room&id={{ $dolly->id }}">
                    <button class="btn btn-primary">Exécuter</button>
                </a>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
                <div class="d-flex align-items-left">
                    <i class="bi bi-x-circle" style="margin-right: 10px;"></i>
                    Supprimer de la base
                </div>
                <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=delete&id={{ $dolly->id }}">
                    <button class="btn btn-primary">Exécuter</button>
                </a>
            </li>
        </ul>






@elseif($dolly->type->name == 'slip')
<ul class="list-group">
    <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
        <div class="d-flex align-items-left">
            <i class="bi bi-trash" style="margin-right: 10px;"></i>
            Vider le chariot
        </div>
        <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=clean&id={{ $dolly->id }}">
            <button class="btn btn-primary">Exécuter</button>
        </a>
    </li>
    <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
        <div class="d-flex align-items-left">
            <i class="bi bi-pencil-square" style="margin-right: 10px;"></i>
            Changer le status des bordereaux
        </div>
        <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=status&id={{ $dolly->id }}">
            <button class="btn btn-primary">Exécuter</button>
        </a>
    </li>
    <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
        <div class="d-flex align-items-left">
            <i class="bi bi-x-circle" style="margin-right: 10px;"></i>
            Supprimer de la base
        </div>
        <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=delete&id={{ $dolly->id }}">
            <button class="btn btn-primary">Exécuter</button>
        </a>
    </li>
</ul>







    @elseif($dolly->type->name == 'slip_record')
        <ul class="list-group">
            <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
                <div class="d-flex align-items-left">
                    <i class="bi bi-trash" style="margin-right: 10px;"></i>
                    Vider le chariot
                </div>
                <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=clean&id={{ $dolly->id }}">
                    <button class="btn btn-primary">Exécuter</button>
                </a>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
                <div class="d-flex align-items-left">
                    <i class="bi bi-box-seam" style="margin-right: 10px;"></i>
                    Changer les boîtes d'archives
                </div>
                <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=container&id={{ $dolly->id }}">
                    <button class="btn btn-primary">Exécuter</button>
                </a>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
                <div class="d-flex align-items-left">
                    <i class="bi bi-list-task" style="margin-right: 10px;"></i>
                    Changer les classes d'activité
                </div>
                <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=activity&id={{ $dolly->id }}">
                    <button class="btn btn-primary">Exécuter</button>
                </a>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
                <div class="d-flex align-items-left">
                    <i class="bi bi-textarea-t" style="margin-right: 10px;"></i>
                    Changer le niveau de description
                </div>
                <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=level&id={{ $dolly->id }}">
                    <button class="btn btn-primary">Exécuter</button>
                </a>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
                <div class="d-flex align-items-left">
                    <i class="bi bi-calendar-event" style="margin-right: 10px;"></i>
                    Changer les dates
                </div>
                <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=dates&id={{ $dolly->id }}">
                    <button class="btn btn-primary">Exécuter</button>
                </a>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
                <div class="d-flex align-items-left">
                    <i class="bi bi-calendar-event" style="margin-right: 10px;"></i>
                    Changer de support
                </div>
                <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=support&id={{ $dolly->id }}">
                    <button class="btn btn-primary">Exécuter</button>
                </a>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center" style="margin-left: 10px;">
                <div class="d-flex align-items-left">
                    <i class="bi bi-x-circle" style="margin-right: 10px;"></i>
                    Supprimer de la base
                </div>
                <a href="{{ route('dollies.action')}}?categ={{ $dolly->type->name }}&action=delete&id={{ $dolly->id }}">
                    <button class="btn btn-primary">Exécuter</button>
                </a>
            </li>
        </ul>
    @endif
</div>
@endsection
