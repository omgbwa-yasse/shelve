<div class="row g-4">
    @foreach ([
        ['icon' => 'bi-trash', 'text' => 'Vider le chariot', 'action' => 'clean', 'color' => 'danger'],
        ['icon' => 'bi-layers', 'text' => 'Changer le niveau de description', 'action' => 'level', 'color' => 'primary'],
        ['icon' => 'bi-shield-check', 'text' => 'Changer le status des descriptions', 'action' => 'status', 'color' => 'success'],
        ['icon' => 'bi-archive', 'text' => 'Changer les boites/chronos d\'archives', 'action' => 'container', 'color' => 'info'],
        ['icon' => 'bi-diagram-3', 'text' => 'Changer la classe d\'activité', 'action' => 'activity', 'color' => 'warning'],
        ['icon' => 'bi-calendar', 'text' => 'Changer de dates', 'action' => 'dates', 'color' => 'secondary'],
        ['icon' => 'bi-file-earmark-arrow-down', 'text' => 'Exporter l\'instrument de recherche', 'action' => 'export', 'color' => 'primary'],
        ['icon' => 'bi-printer', 'text' => 'Imprimer l\'instrument de recherche', 'action' => 'print', 'color' => 'dark'],
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
