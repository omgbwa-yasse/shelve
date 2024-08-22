@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <!-- Main Heading -->
        <h1 class="mb-4">Liste des enregistrements</h1>

        <!-- Action and Search Bar -->
        <div class="row mb-3">
            <div class="col-md-8">
                <a href="{{ route('records.create') }}" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-circle"></i> Nouveau enregistrement
                </a>
            </div>
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-primary text-white"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Rechercher un enregistrement...">
                </div>
            </div>
        </div>

        <!-- Records List -->
        <div id="recordList">
            @foreach ($records as $record)
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-9">
                                <!-- Card Title -->
                                <h5 class="card-title mb-2">
                                    <b>{{ $record->code }} </b> - {{ $record->name }}
                                    <span class="badge bg-{{ $record->level->color ?? 'secondary' }}">
                                        {{ $record->level->name ?? 'N/A' }}
                                    </span>
                                </h5>
                                <!-- Card Content -->
                                <p class="card-text">
                                    <strong>Content:</strong> {{ $record->content }}<br>
                                    <strong>| Level:</strong> {{ $record->level->name ?? 'N/A' }}
                                    <strong>| Status:</strong> {{ $record->status->name ?? 'N/A' }}
                                    <strong>| Support:</strong> {{ $record->support->name ?? 'N/A' }}
                                    <strong>| Activity:</strong> {{ $record->activity->name ?? 'N/A' }}
                                    <strong>| Dates:</strong> {{ $record->date_start ?? 'N/A' }} - {{ $record->date_end ?? 'N/A' }}
                                    <strong>| Location:</strong> {{ $record->location_original ?? 'N/A' }}
                                    <strong>| Authors:</strong> {{ $record->authors->pluck('name')->join(', ') ?? 'N/A' }}
                                </p>
                            </div>
                            <div class="col-md-3 text-md-end text-center">
                                <!-- Action Buttons -->
                                <div class="d-flex justify-content-md-end justify-content-center align-items-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('records.show', $record) }}" class="btn btn-sm btn-outline-secondary" title="Voir">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('records.edit', $record) }}" class="btn btn-sm btn-outline-primary" title="Modifier">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('records.destroy', $record) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $records->links() }}
        </div>
    </div>

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
