@extends('layouts.app')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<style>
/* ── Buildings Index ── */
:root {
    --bld-slate-950: #0c1524;
    --bld-slate-900: #1a2236;
    --bld-slate-800: #253047;
    --bld-slate-700: #344060;
    --bld-slate-500: #5a6a8a;
    --bld-slate-300: #9aaac4;
    --bld-slate-100: #e8edf6;
    --bld-slate-50:  #f4f6fb;
    --bld-amber:     #d97706;
    --bld-amber-lt:  #fef3c7;
    --bld-emerald:   #059669;
    --bld-red:       #dc2626;
    --bld-radius:    10px;
    --bld-radius-lg: 16px;
    --bld-shadow-sm: 0 1px 3px rgba(12,21,36,.06), 0 1px 2px rgba(12,21,36,.04);
    --bld-shadow:    0 4px 12px rgba(12,21,36,.08), 0 2px 4px rgba(12,21,36,.05);
    --bld-shadow-lg: 0 12px 32px rgba(12,21,36,.12), 0 4px 8px rgba(12,21,36,.06);
}

.bld-page {
    font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, sans-serif;
    background: var(--bld-slate-50);
    min-height: 100vh;
    padding: 2rem 0 4rem;
}

/* ── Page header ── */
.bld-page-header {
    background: linear-gradient(135deg, var(--bld-slate-900) 0%, var(--bld-slate-800) 60%, var(--bld-slate-700) 100%);
    border-radius: var(--bld-radius-lg);
    padding: 2rem 2.25rem;
    margin-bottom: 1.5rem;
    position: relative;
    overflow: hidden;
    box-shadow: var(--bld-shadow-lg);
}

.bld-page-header::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse 60% 80% at 90% 50%, rgba(217,119,6,.12) 0%, transparent 70%),
        url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.025'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.bld-page-header .bld-header-eyebrow {
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--bld-amber);
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.bld-page-header .bld-header-eyebrow::before {
    content: '';
    display: inline-block;
    width: 20px;
    height: 2px;
    background: var(--bld-amber);
    border-radius: 1px;
}

.bld-page-title {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 2rem;
    font-weight: 700;
    color: #fff;
    margin: 0 0 0.25rem;
    line-height: 1.2;
}

.bld-page-subtitle {
    color: var(--bld-slate-300);
    font-size: 0.9rem;
    margin: 0;
}

.bld-header-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.bld-btn-primary {
    background: var(--bld-amber);
    color: #fff;
    border: none;
    padding: 0.65rem 1.4rem;
    border-radius: var(--bld-radius);
    font-family: 'DM Sans', sans-serif;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    white-space: nowrap;
    box-shadow: 0 2px 8px rgba(217,119,6,.35);
}

.bld-btn-primary:hover {
    background: #b45309;
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(217,119,6,.4);
}

.bld-search-input {
    background: rgba(255,255,255,.1);
    border: 1px solid rgba(255,255,255,.2);
    border-radius: var(--bld-radius);
    color: #fff;
    padding: 0.6rem 1rem 0.6rem 2.5rem;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.875rem;
    width: 220px;
    transition: all .2s ease;
    backdrop-filter: blur(4px);
}

.bld-search-input::placeholder { color: rgba(255,255,255,.5); }
.bld-search-input:focus {
    outline: none;
    background: rgba(255,255,255,.15);
    border-color: rgba(255,255,255,.4);
    width: 260px;
    box-shadow: 0 0 0 3px rgba(255,255,255,.08);
}

.bld-search-wrap {
    position: relative;
}

.bld-search-wrap i {
    position: absolute;
    left: 0.85rem;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(255,255,255,.5);
    font-size: 0.85rem;
    pointer-events: none;
}

/* ── Stats bar ── */
.bld-stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.bld-stat-card {
    background: #fff;
    border: 1px solid var(--bld-slate-100);
    border-radius: var(--bld-radius);
    padding: 1.1rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: var(--bld-shadow-sm);
    transition: all .2s ease;
}

.bld-stat-card:hover {
    box-shadow: var(--bld-shadow);
    transform: translateY(-2px);
}

.bld-stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.bld-stat-icon-primary   { background: #dbeafe; color: #1d4ed8; }
.bld-stat-icon-success   { background: #d1fae5; color: #059669; }
.bld-stat-icon-danger    { background: #fee2e2; color: #dc2626; }
.bld-stat-icon-info      { background: #e0f2fe; color: #0284c7; }

.bld-stat-value {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 1.6rem;
    font-weight: 700;
    color: var(--bld-slate-900);
    line-height: 1;
    margin-bottom: 0.15rem;
}

.bld-stat-label {
    font-size: 0.78rem;
    color: var(--bld-slate-500);
    font-weight: 500;
}

/* ── Table card ── */
.bld-table-card {
    background: #fff;
    border: 1px solid var(--bld-slate-100);
    border-radius: var(--bld-radius-lg);
    overflow: hidden;
    box-shadow: var(--bld-shadow);
}

.bld-table-toolbar {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--bld-slate-100);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.bld-table-toolbar-title {
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--bld-slate-500);
}

table.bld-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
}

.bld-table thead th {
    background: var(--bld-slate-50);
    color: var(--bld-slate-500);
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 0.7rem 1.1rem;
    border-bottom: 2px solid var(--bld-slate-100);
    white-space: nowrap;
}

.bld-table tbody td {
    padding: 0.9rem 1.1rem;
    border-bottom: 1px solid var(--bld-slate-100);
    color: var(--bld-slate-900);
    vertical-align: middle;
}

.bld-table tbody tr:last-child td { border-bottom: none; }

.bld-table tbody tr {
    transition: background .15s ease;
}

.bld-table tbody tr:hover {
    background: var(--bld-slate-50);
}

.bld-building-link {
    font-weight: 600;
    color: var(--bld-slate-900);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.6rem;
    transition: color .15s ease;
}

.bld-building-link:hover { color: var(--bld-amber); }

.bld-building-link .bld-icon-wrap {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: var(--bld-slate-100);
    color: var(--bld-slate-500);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    flex-shrink: 0;
    transition: all .15s ease;
}

.bld-building-link:hover .bld-icon-wrap {
    background: var(--bld-amber-lt);
    color: var(--bld-amber);
}

/* Visibility pill */
.bld-vis-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.28rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.02em;
}

.bld-vis-public  { background: #d1fae5; color: #065f46; }
.bld-vis-private { background: #fee2e2; color: #991b1b; }
.bld-vis-other   { background: #fef3c7; color: #92400e; }

.bld-vis-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
}

.bld-vis-public  .bld-vis-dot { background: #059669; }
.bld-vis-private .bld-vis-dot { background: #dc2626; }
.bld-vis-other   .bld-vis-dot { background: #d97706; }

/* Count badge */
.bld-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 26px;
    height: 26px;
    padding: 0 0.5rem;
    border-radius: 6px;
    background: var(--bld-slate-100);
    color: var(--bld-slate-700);
    font-size: 0.8rem;
    font-weight: 700;
}

/* Description cell */
.bld-desc {
    color: var(--bld-slate-500);
    font-size: 0.82rem;
    max-width: 240px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Date */
.bld-date {
    color: var(--bld-slate-500);
    font-size: 0.82rem;
    white-space: nowrap;
}

/* Actions */
.bld-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.3rem;
}

.bld-action-btn {
    width: 32px;
    height: 32px;
    border-radius: 7px;
    border: 1px solid var(--bld-slate-100);
    background: #fff;
    color: var(--bld-slate-500);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    text-decoration: none;
    transition: all .15s ease;
    cursor: pointer;
}

.bld-action-btn:hover {
    border-color: var(--bld-slate-300);
    color: var(--bld-slate-900);
    background: var(--bld-slate-50);
    transform: translateY(-1px);
    box-shadow: var(--bld-shadow-sm);
}

.bld-action-btn.danger:hover {
    border-color: #fca5a5;
    color: var(--bld-red);
    background: #fff5f5;
}

/* Empty state */
.bld-empty {
    padding: 4rem 2rem;
    text-align: center;
    color: var(--bld-slate-500);
}

.bld-empty-icon {
    width: 72px;
    height: 72px;
    border-radius: 20px;
    background: var(--bld-slate-100);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    color: var(--bld-slate-300);
    margin: 0 auto 1.25rem;
}

.bld-empty h5 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 1.1rem;
    color: var(--bld-slate-700);
    margin-bottom: 0.5rem;
}

.bld-empty p {
    font-size: 0.875rem;
    margin-bottom: 1.5rem;
}

/* Pagination */
.bld-pagination {
    padding: 1rem 1.25rem;
    border-top: 1px solid var(--bld-slate-100);
    display: flex;
    justify-content: center;
}

/* Responsive */
@media (max-width: 992px) {
    .bld-stats-row { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 600px) {
    .bld-stats-row { grid-template-columns: repeat(2, 1fr); }
    .bld-page-title { font-size: 1.5rem; }
    .bld-search-input { width: 160px; }
    .bld-search-input:focus { width: 180px; }
}

/* Animate rows in */
@keyframes bldRowIn {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}

.bld-table tbody tr {
    animation: bldRowIn .3s ease both;
}

.bld-table tbody tr:nth-child(1) { animation-delay: .04s; }
.bld-table tbody tr:nth-child(2) { animation-delay: .08s; }
.bld-table tbody tr:nth-child(3) { animation-delay: .12s; }
.bld-table tbody tr:nth-child(4) { animation-delay: .16s; }
.bld-table tbody tr:nth-child(5) { animation-delay: .20s; }
.bld-table tbody tr:nth-child(6) { animation-delay: .24s; }
.bld-table tbody tr:nth-child(n+7) { animation-delay: .28s; }
</style>
@endpush

@section('content')
@php
    $totalBuildings = $buildings->total();
    $publicBuildings = $buildings->where('visibility', 'public')->count();
    $privateBuildings = $buildings->where('visibility', 'private')->count();
    $floorsCount = $buildings->sum(fn($b) => $b->floors->count());
@endphp

<div class="bld-page">
    <div class="container-fluid px-3 px-md-4">

        {{-- Page Header --}}
        <div class="bld-page-header">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                <div>
                    <div class="bld-header-eyebrow">Infrastructure</div>
                    <h1 class="bld-page-title">Gestion des Bâtiments</h1>
                    <p class="bld-page-subtitle">Vue d'ensemble de vos infrastructures de conservation</p>
                </div>
                <div class="bld-header-actions">
                    <div class="bld-search-wrap">
                        <i class="bi bi-search"></i>
                        <input type="text" class="bld-search-input" id="searchBuilding" placeholder="Filtrer...">
                    </div>
                    <a href="{{ route('buildings.create') }}" class="bld-btn-primary">
                        <i class="bi bi-plus-lg"></i> Nouveau bâtiment
                    </a>
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="bld-stats-row">
            <div class="bld-stat-card">
                <div class="bld-stat-icon bld-stat-icon-primary"><i class="bi bi-building"></i></div>
                <div>
                    <div class="bld-stat-value">{{ $totalBuildings }}</div>
                    <div class="bld-stat-label">Bâtiments</div>
                </div>
            </div>
            <div class="bld-stat-card">
                <div class="bld-stat-icon bld-stat-icon-success"><i class="bi bi-globe"></i></div>
                <div>
                    <div class="bld-stat-value">{{ $publicBuildings }}</div>
                    <div class="bld-stat-label">Publics</div>
                </div>
            </div>
            <div class="bld-stat-card">
                <div class="bld-stat-icon bld-stat-icon-danger"><i class="bi bi-lock"></i></div>
                <div>
                    <div class="bld-stat-value">{{ $privateBuildings }}</div>
                    <div class="bld-stat-label">Privés</div>
                </div>
            </div>
            <div class="bld-stat-card">
                <div class="bld-stat-icon bld-stat-icon-info"><i class="bi bi-layers"></i></div>
                <div>
                    <div class="bld-stat-value">{{ $floorsCount }}</div>
                    <div class="bld-stat-label">Étages au total</div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bld-table-card">
            <div class="bld-table-toolbar">
                <span class="bld-table-toolbar-title">{{ $totalBuildings }} bâtiment{{ $totalBuildings > 1 ? 's' : '' }}</span>
            </div>

            <div class="table-responsive">
                <table class="bld-table" id="buildingTable">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Visibilité</th>
                            <th class="text-center">Étages</th>
                            <th class="text-center">Salles</th>
                            <th>Description</th>
                            <th>Créé le</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($buildings as $building)
                            <tr>
                                <td>
                                    <a href="{{ route('buildings.show', $building->id) }}" class="bld-building-link">
                                        <span class="bld-icon-wrap"><i class="bi bi-building"></i></span>
                                        {{ $building->name ?? 'N/A' }}
                                    </a>
                                </td>
                                <td>
                                    @php
                                        $vis = $building->visibility ?? '';
                                        $visClass = match($vis) {
                                            'public'  => 'bld-vis-public',
                                            'private' => 'bld-vis-private',
                                            default   => 'bld-vis-other',
                                        };
                                        $visLabel = match($vis) {
                                            'public'  => 'Public',
                                            'private' => 'Privé',
                                            default   => ucfirst($vis ?: 'N/A'),
                                        };
                                    @endphp
                                    <span class="bld-vis-pill {{ $visClass }}">
                                        <span class="bld-vis-dot"></span>
                                        {{ $visLabel }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="bld-count">{{ $building->floors->count() }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="bld-count">{{ $building->floors->sum(fn($f) => $f->rooms->count()) }}</span>
                                </td>
                                <td>
                                    <span class="bld-desc" title="{{ $building->description }}">
                                        {{ $building->description ?: '—' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="bld-date">{{ $building->created_at?->format('d/m/Y') ?? '—' }}</span>
                                </td>
                                <td>
                                    <div class="bld-actions">
                                        <a href="{{ route('buildings.show', $building->id) }}" class="bld-action-btn" title="Voir">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('buildings.edit', $building->id) }}" class="bld-action-btn" title="Modifier">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('buildings.destroy', $building->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Supprimer « {{ $building->name }} » ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="bld-action-btn danger" title="Supprimer">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="bld-empty">
                                        <div class="bld-empty-icon"><i class="bi bi-building-slash"></i></div>
                                        <h5>Aucun bâtiment enregistré</h5>
                                        <p>Commencez par créer votre premier bâtiment pour organiser vos espaces de conservation.</p>
                                        <a href="{{ route('buildings.create') }}" class="bld-btn-primary">
                                            <i class="bi bi-plus-lg"></i> Créer un bâtiment
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($buildings, 'hasPages') && $buildings->hasPages())
                <div class="bld-pagination">
                    {{ $buildings->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('searchBuilding')?.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#buildingTable tbody tr').forEach(row => {
        const match = row.textContent.toLowerCase().includes(q);
        row.style.display = match ? '' : 'none';
    });
});
</script>
@endpush
