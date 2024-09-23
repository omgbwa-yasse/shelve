@extends('layouts.app')
<style>
    a {
        text-decoration: none;
        color: #0178d4;
    }
</style>
@section('content')
    <div class="container-fluid mt-4">
        <h1 class="mb-4"><i class="bi bi-list-ul me-2"></i>Inventaire des archives {{ $title ?? ''}}</h1>
            <div class="d-flex justify-content-between align-items-center bg-light p-3 mb-3">
                <div class="d-flex align-items-center">
                    <a href="#" class="btn btn-light btn-sm me-2">
                        <i class="bi bi-cart me-1"></i>
                        Chariot
                    </a>
                    <a href="#" class="btn btn-light btn-sm me-2">
                        <i class="bi bi-download me-1"></i>
                        Exporter
                    </a>
                    <a href="#" class="btn btn-light btn-sm me-2">
                        <i class="bi bi-printer me-1"></i>
                        Imprimer
                    </a>
                    <a href="" class="btn btn-light btn-sm me-2">
                        <i class="bi bi-list-task me-1"></i>
                        Tâches
                    </a>
                    <a href="" class="btn btn-light btn-sm me-2">
                        <i class="bi bi-calendar-check me-1"></i>
                        Réservations
                    </a>
                </div>
                <div class="d-flex align-items-center">
                    <a href="#" class="btn btn-light btn-sm me-2">
                        <i class="bi bi-arrow-repeat me-1"></i>
                        Transférer
                    </a>
                    <a href="#" class="btn btn-light btn-sm me-2">
                        <i class="bi bi-envelope me-1"></i>
                        Communiqué
                    </a>
                    <a href="#" class="btn btn-light btn-sm">
                        <i class="bi bi-check-square me-1"></i>
                        Tout chocher
                    </a>
                </div>
            </div>


    <div id="recordList">
       @foreach ($records as $record)
        <div class="form-check">
            <a href="{{ route('records.show', $record) }}">
            <input class="form-check-input" type="checkbox" value="{{$record->id}}" id="" />
            <label class="form-check-label" for="">
                <span style="font-size: 1.6em; font-weight: bold;">{{ $record->code }}  - {{ $record->name }}</span>
            </label>
            </a>
        </div>
            <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-12">
                                <p class="card-text">
                                    <i class="bi bi-card-text me-2"></i> Content : {{ $record->content }}<br>
                                    <i class="bi bi-bar-chart-fill me-2"></i>Niveau de description :  <a href="{{ route('records.sort')}}?categ=level&id={{ $record->level->id ?? ''}}">{{ $record->level->name ?? 'N/A' }}</a>
                                    <i class="bi bi-flag-fill me-2"></i>Statut : <a href=" {{route('records.sort')}}?categ=status&id={{ $record->status->id ?? 'N/A' }}">{{ $record->status->name ?? 'N/A' }}</a>
                                    <i class="bi bi-hdd-fill me-2"></i>Support : <a href="{{ route('records.sort')}}?categ=support&id={{ $record->support->id ?? 'N/A' }}">{{ $record->support->name ?? 'N/A' }}</a>
                                    <i class="bi bi-activity me-2"></i>Activité : <a href="{{ route('records.sort')}}?categ=activity&id={{ $record->activity->id ?? 'N/A' }}">{{ $record->activity->name ?? 'N/A' }}</a>
                                    <i class="bi bi-calendar-event me-2"></i>Dates : <a href="{{ route('records.sort')}}?categ=dates&id=">{{ $record->date_start ?? 'N/A' }} - {{ $record->date_end ?? 'N/A' }}</a>
                                    <i class="bi bi-geo-alt-fill me-2"></i>Contenant : <a href="{{ route('records.sort')}}?categ=container&id={{ $record->container->id ?? 'none' }}">{{ $record->container->name ?? 'Non conditionné' }}</a>
                                    <i class="bi bi-people-fill me-2"></i>Producteur : <a href="{{ route('records.sort')}}?categ=authors&id={{ $record->authors->pluck('id')->join('') }}">{{ $record->authors->pluck('name')->join(', ') ?? 'N/A' }}</a>
                                </p>
                                <strong>Vedettes : </strong>
                                <p class="card-text">
                                    @foreach($record->terms as $index => $term)
                                        <a href="{{ route('records.sort')}}?categ=term&id={{ $term->id ?? 'N/A' }}"> {{ $term->name ?? 'N/A' }} </a>
                                        @if(!$loop->last)
                                            {{ " ; " }}
                                        @endif
                                    @endforeach
                                </p>

                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>



    <footer class="bg-light py-3">
        <div class="container">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item {{ $records->currentPage() == 1 ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $records->previousPageUrl() }}" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    @for ($i = 1; $i <= $records->lastPage(); $i++)
                        <li class="page-item {{ $records->currentPage() == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ $records->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor
                    <li class="page-item {{ $records->currentPage() == $records->lastPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $records->nextPageUrl() }}" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </footer>

    @push('scripts')
        <script>
            document.getElementById('searchInput').addEventListener('keyup', function() {
                var input, filter, cards, card, i, txtValue;
                input = document.getElementById('searchInput');
                filter = input.value.toUpperCase();
                cards = document.getElementById('recordList').getElementsByClassName('card');

                for (i = 0; i < cards.length; i++) {
                    card = cards[i];
                    txtValue = card.textContent || card.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        card.style.display = "";
                    } else {
                        card.style.display = "none";
                    }
                }
            });
        </script>
    @endpush
@endsection
