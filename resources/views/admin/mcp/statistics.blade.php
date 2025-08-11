@extends('layouts.app')
@section('title','Statistiques LLM')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0"><i class="bi bi-bar-chart me-2"></i>Statistiques LLM</h1>
        <a href="{{ route('admin.mcp.dashboard') }}" class="btn btn-sm btn-outline-secondary">Dashboard</a>
    </div>

    <form method="GET" class="mb-3">
        @php($p = $period ?? request('period','month'))
        <div class="btn-group btn-group-sm" role="group">
            <a class="btn {{ $p==='day'?'btn-primary':'btn-outline-primary' }}" href="?period=day">1j</a>
            <a class="btn {{ $p==='week'?'btn-primary':'btn-outline-primary' }}" href="?period=week">7j</a>
            <a class="btn {{ $p==='month'?'btn-primary':'btn-outline-primary' }}" href="?period=month">30j</a>
            <a class="btn {{ $p==='year'?'btn-primary':'btn-outline-primary' }}" href="?period=year">365j</a>
        </div>
    </form>

    @php($total = $stats['by_model']->sum('requests'))
    @php($tokens = $stats['by_model']->sum('tokens'))
    @php($cost = $stats['by_model']->sum('cost')/1_000_000)
    @php($success = $stats['status_breakdown']['success'] ?? 0)
    <div class="row g-3 mb-2 small">
        <div class="col-md-3"><div class="card h-100"><div class="card-body py-2"><div class="text-muted">Requêtes</div><div class="fs-5 fw-semibold">{{ $total }}</div></div></div></div>
        <div class="col-md-3"><div class="card h-100"><div class="card-body py-2"><div class="text-muted">Tokens</div><div class="fs-6 fw-semibold">{{ number_format($tokens) }}</div></div></div></div>
        <div class="col-md-3"><div class="card h-100"><div class="card-body py-2"><div class="text-muted">Coût (USD)</div><div class="fs-6 fw-semibold">${{ number_format($cost,4) }}</div></div></div></div>
        <div class="col-md-3"><div class="card h-100"><div class="card-body py-2"><div class="text-muted">Succès</div><div class="fs-6 fw-semibold">{{ $total? round($success*100/$total,1):0 }}%</div></div></div></div>
    </div>

    <div class="card mb-3">
        <div class="card-header py-2 small fw-semibold">Évolution quotidienne</div>
        <div class="card-body p-2">
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle mb-0 small">
                    <thead><tr><th>Date</th><th>Req.</th><th>Tokens</th><th>Coût ($)</th><th>Latence ms</th><th>Succès</th></tr></thead>
                    <tbody>
                    @foreach(app(\App\Services\Llm\LlmMetricsService::class)->getTimeSeries($stats['period_days']) as $row)
                        <tr>
                            <td>{{ $row['date'] }}</td>
                            <td>{{ $row['requests'] }}</td>
                            <td>{{ number_format($row['tokens']) }}</td>
                            <td>{{ number_format($row['cost_microusd']/1_000_000,4) }}</td>
                            <td>{{ $row['avg_latency_ms'] }}</td>
                            <td>{{ $row['success_rate'] }}%</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header py-2 small fw-semibold">Par Modèle</div>
        <div class="card-body p-2">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0 small">
                    <thead><tr><th>Provider</th><th>Modèle</th><th>Req.</th><th>Tokens</th><th>Coût ($)</th><th>Latence moy.</th><th>Succès %</th></tr></thead>
                    <tbody>
                    @foreach($stats['by_model'] as $m)
                        @php($tot=$m->requests?:1)
                        <tr>
                            <td>{{ $m->provider }}</td>
                            <td>{{ $m->model }}</td>
                            <td>{{ $m->requests }}</td>
                            <td>{{ number_format($m->tokens) }}</td>
                            <td>{{ number_format($m->cost/1_000_000,4) }}</td>
                            <td>{{ (int)$m->avg_latency }} ms</td>
                            <td>{{ round(($m->success_count*100)/$tot,1) }}%</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header py-2 small fw-semibold">Top Erreurs</div>
        <div class="card-body p-2">
            @php($fail = app(\App\Services\Llm\LlmMetricsService::class)->getTopFailures($stats['period_days']))
            <table class="table table-sm mb-0 small"><thead><tr><th>Code</th><th>Occurrences</th></tr></thead><tbody>
                @forelse($fail as $f)
                    <tr><td>{{ $f->code }}</td><td>{{ $f->c }}</td></tr>
                @empty
                    <tr><td colspan="2" class="text-muted">Aucune erreur</td></tr>
                @endforelse
            </tbody></table>
        </div>
    </div>
</div>
@endsection
