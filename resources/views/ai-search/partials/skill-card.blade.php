<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h6 class="mb-1">
                    @if($skill['record']->location === 'system')
                        <span class="badge bg-secondary me-1">Système</span>
                    @else
                        <span class="badge bg-primary me-1">Personnalisé</span>
                    @endif
                    {{ $skill['record']->name }}
                    @if($skill['record']->version)
                        <small class="text-muted">v{{ $skill['record']->version }}</small>
                    @endif
                </h6>
                <div class="text-muted small mb-1">{{ $skill['record']->description ?? '—' }}</div>
                <div class="small text-muted">
                    <i class="bi bi-folder me-1"></i>{{ $skill['record']->folder }}
                    @if(count($skill['resources']) > 0)
                        · {{ count($skill['resources']) }} ressource(s)
                    @endif
                </div>
            </div>
            <div class="d-flex gap-1">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse"
                        data-bs-target="#skillFiles-{{ $skill['record']->id }}" title="Voir les fichiers">
                    <i class="bi bi-folder2-open"></i>
                </button>
                <form method="POST" action="{{ route('ai-search.skills.toggle', $skill['record']) }}">
                    @csrf
                    <button class="btn btn-sm {{ $skill['record']->enabled ? 'btn-success' : 'btn-outline-secondary' }}" title="{{ $skill['record']->enabled ? 'Désactiver' : 'Activer' }}">
                        <i class="bi {{ $skill['record']->enabled ? 'bi-check-circle' : 'bi-x-circle' }}"></i>
                    </button>
                </form>
                @if($skill['record']->location === 'custom')
                    <form method="POST" action="{{ route('ai-search.skills.destroy', $skill['record']) }}"
                          onsubmit="return confirm('Supprimer ce skill ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                    </form>
                @endif
            </div>
        </div>
    </div>
    <div class="collapse" id="skillFiles-{{ $skill['record']->id }}">
        <div class="card-footer bg-light">
            <strong class="small">SKILL.md</strong>
            <pre class="border rounded p-2 bg-white mt-1" style="white-space:pre-wrap; max-height:220px; overflow:auto; font-size:0.8rem;">{{ \Illuminate\Support\Str::limit(file_exists($skill['skill_md']) ? file_get_contents($skill['skill_md']) : '', 4000) }}</pre>
            @if(count($skill['resources']) > 0)
                <strong class="small">Ressources</strong>
                <ul class="small mt-1 mb-0">
                    @foreach($skill['resources'] as $res)
                        <li><i class="bi bi-file-earmark me-1"></i>{{ $res['relative'] }} ({{ number_format($res['size'] / 1024, 1) }} Ko)</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
