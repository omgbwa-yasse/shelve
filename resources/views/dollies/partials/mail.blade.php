
<div class="row g-4"> @foreach ([
        ['icon' => 'bi-trash', 'text' => 'Vider le chariot', 'action' => 'clean', 'color' => 'danger'],
        ['icon' => 'bi-calendar', 'text' => 'Changer les dates des courriers', 'action' => 'dates', 'color' => 'secondary'],
        ['icon' => 'bi-exclamation-circle', 'text' => 'Changer la priorité des courriers', 'action' => 'priority', 'color' => 'warning'],
        ['icon' => 'bi-archive', 'text' => 'Archiver les courriers', 'action' => 'archive', 'color' => 'info'],
        ['icon' => 'bi-file-earmark-arrow-down', 'text' => 'Exporter la liste du courrier', 'action' => 'export', 'color' => 'primary'],
        ['icon' => 'bi-printer', 'text' => 'Imprimer la liste de courrier', 'action' => 'print', 'color' => 'dark'],
        ['icon' => 'bi-x-circle', 'text' => 'Supprimer de la base', 'action' => 'delete', 'color' => 'danger'],
    ] as $item)
        <div class="col-md-4 col-sm-6">
            <div class="card action-card h-100">
                <div class="card-body text-center">
                    <i class="bi {{ $item['icon'] }} action-icon text-primary"></i>
                    <h5 class="card-title">{{ $item['text'] }}</h5>
                </div>
                <a href="{{ route('dollies.action', ['categ' => $dolly->type->name, 'action' => $item['action'], 'id' => $dolly->id]) }}" class="btn btn-primary btn-action">Exécuter</a>
            </div>
        </div>
    @endforeach
</div>
