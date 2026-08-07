<div class="row g-4">
    @foreach ([
        ['icon' => 'bi-trash', 'text' => 'Vider le chariot', 'action' => 'clean', 'color' => 'danger'],
        ['icon' => 'bi-box-seam', 'text' => 'Changer les boîtes d\'archives', 'action' => 'container', 'color' => 'primary'],
        ['icon' => 'bi-list-task', 'text' => 'Changer les classes d\'activité', 'action' => 'activity', 'color' => 'info'],
        ['icon' => 'bi-textarea-t', 'text' => 'Changer le niveau de description', 'action' => 'level', 'color' => 'warning'],
        ['icon' => 'bi-calendar-event', 'text' => 'Changer les dates', 'action' => 'dates', 'color' => 'secondary'],
        ['icon' => 'bi-calendar-event', 'text' => 'Changer de support', 'action' => 'support', 'color' => 'success'],
        ['icon' => 'bi-cpu', 'text' => 'Formater les titres avec IA', 'action' => 'ai_format_titles', 'color' => 'info'],
        ['icon' => 'bi-file-text', 'text' => 'Générer des résumés avec IA', 'action' => 'ai_generate_summaries', 'color' => 'success'],
        ['icon' => 'bi-tags', 'text' => 'Extraire des mots-clés avec IA', 'action' => 'ai_extract_keywords', 'color' => 'warning'],
        ['icon' => 'bi-book', 'text' => 'Mettre à jour les index thésaurus', 'action' => 'ai_update_thesaurus', 'color' => 'primary'],
        ['icon' => 'bi-magic', 'text' => 'Reformuler les intitulés avec IA', 'action' => 'ai_reformulate_titles', 'color' => 'info'],
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
