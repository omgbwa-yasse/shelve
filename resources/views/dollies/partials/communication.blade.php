<div class="row g-4">
    @foreach ([
        ['icon' => 'bi-trash', 'text' => 'Vider le chariot', 'action' => 'clean', 'color' => 'danger'],
        ['icon' => 'bi-calendar-date', 'text' => 'Changer la date de retour', 'action' => 'return_date', 'color' => 'secondary'],
        ['icon' => 'bi-calendar-check', 'text' => 'Changer la date effective de retour', 'action' => 'return_date_effective', 'color' => 'warning'],
        ['icon' => 'bi-shuffle', 'text' => 'Changer le status de communication', 'action' => 'status', 'color' => 'info'],
        ['icon' => 'bi-x-circle', 'text' => 'Supprimer de la base', 'action' => 'delete', 'color' => 'danger'],
    ] as $item)
        <div class="col-md-4 col-sm-6">
            <div class="card action-card h-100">
                <div class="card-body text-center">
                    <i class="bi {{ $item['icon'] }} action-icon text-primary"></i>
                    <h5 class="card-title">{{ $item['text'] }}</h5>
                </div>
                <a href="{{ route('dollies.action', ['categ' => $dolly->category, 'action' => $item['action'], 'id' => $dolly->id]) }}" class="btn btn-primary btn-action">Exécuter</a>
            </div>
        </div>
    @endforeach
</div>
