@extends('layouts.app')
@section('title','État de Santé MCP')

@push('styles')
<style>
    .status-badge{padding:.30rem .6rem;border-radius:1rem;font-size:.65rem;font-weight:600;display:inline-flex;align-items:center;gap:.25rem}
    .status-ok{background:#198754;color:#fff}.status-warning{background:#ffc107;color:#212529}.status-error{background:#dc3545;color:#fff}.status-unknown{background:#6c757d;color:#fff}
    .mini-table th,.mini-table td{vertical-align:middle;font-size:.8rem}
    .recommendation{border-left:4px solid #0dcaf0;background:#f1fbff;padding:.5rem .65rem;border-radius:4px;font-size:.75rem;margin-bottom:.5rem}
    .recommendation.error{border-left-color:#dc3545;background:#fff5f5}
    .recommendation.warning{border-left-color:#ffc107;background:#fffbf0}
    .recommendation.success{border-left-color:#198754;background:#f0fff4}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 m-0"><i class="bi bi-heart-pulse text-danger me-2"></i>État de Santé MCP</h1>
        <div class="btn-group btn-group-sm">
            <a href="{{ route('admin.mcp.dashboard') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Dashboard</a>
            <a href="{{ url()->current() }}" class="btn btn-primary"><i class="bi bi-arrow-clockwise me-1"></i>Actualiser</a>
        </div>
    </div>

    @php $overall=$health['overall_status']??'unknown'; @endphp
    <div class="card mb-3">
        <div class="card-body py-2 d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <span class="status-badge status-{{ $overall }}">
                    <i class="bi bi-{{ $overall==='ok'?'check-circle':($overall==='warning'?'exclamation-triangle':($overall==='error'?'x-circle':'hourglass-split')) }}"></i>
                    {{ strtoupper($overall) }}
                </span>
                <span class="ms-3 text-muted small">
                    @if($overall==='ok') Système opérationnel
                    @elseif($overall==='warning') État dégradé
                    @elseif($overall==='error') Problèmes critiques
                    @else Vérification en cours... @endif
                </span>
            </div>
            <small class="text-muted">Chargé à {{ now()->format('H:i:s') }}</small>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header py-2"><strong class="small">Composants</strong></div>
                <div class="table-responsive">
                    <table class="table table-sm mini-table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nom</th>
                                <th class="text-center">Statut</th>
                                <th style="width:90px">Temps</th>
                                <th style="width:110px">Vérif.</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($health as $name=>$component)
                            @continue($name==='overall_status')
                            @php $st=$component['status']??'unknown'; @endphp
                            <tr>
                                <td>
                                    <strong class="small">{{ ucfirst(str_replace('_',' ', $name)) }}</strong>
                                    @if(!empty($component['model_name']))<br><small class="text-muted">{{ $component['model_name'] }}</small>@endif
                                    @if(!empty($component['error']))<div class="text-danger small mt-1">{{ $component['error'] }}</div>@endif
                                </td>
                                <td class="text-center">
                                    <span class="status-badge status-{{ $st }}"><i class="bi bi-{{ $st==='ok'?'check-circle':($st==='warning'?'exclamation-triangle':($st==='error'?'x-circle':'hourglass-split')) }}"></i>{{ strtoupper($st) }}</span>
                                </td>
                                <td class="small">{{ isset($component['response_time'])?number_format($component['response_time'],2):'—' }}</td>
                                <td class="small">{{ isset($component['last_check'])? \Carbon\Carbon::parse($component['last_check'])->format('H:i:s'):'—' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-5 d-flex flex-column">
            <div class="card mb-3 flex-grow-1">
                <div class="card-header py-2"><strong class="small">Recommandations</strong></div>
                <div class="card-body pt-2 pb-2">
                    @forelse($recommendations as $rec)
                        <div class="recommendation {{ $rec['type'] }}">
                            <strong class="d-block mb-1">{{ $rec['title'] }}</strong>
                            <div>{{ $rec['message'] }}</div>
                        </div>
                    @empty
                        <div class="recommendation success mb-0">Système optimal.</div>
                    @endforelse
                </div>
            </div>
            <div class="card">
                <div class="card-header py-2"><strong class="small">Informations Système</strong></div>
                <div class="card-body small pt-2 pb-2">
                    <div class="row g-2">
                        <div class="col-6">PHP</div><div class="col-6 text-end"><code>{{ $systemInfo['php_version'] ?? 'N/A' }}</code></div>
                        <div class="col-6">Laravel</div><div class="col-6 text-end"><code>{{ $systemInfo['laravel_version'] ?? 'N/A' }}</code></div>
                        <div class="col-6">Ollama</div><div class="col-6 text-end"><code>{{ $systemInfo['ollama_version'] ?? 'N/A' }}</code></div>
                        <div class="col-6">Mémoire</div><div class="col-6 text-end"><code>{{ $systemInfo['memory_limit'] ?? 'N/A' }}</code></div>
                        <div class="col-6">Exec max</div><div class="col-6 text-end"><code>{{ $systemInfo['max_execution_time'] ?? 'N/A' }}s</code></div>
                        <div class="col-6">Timezone</div><div class="col-6 text-end"><code>{{ config('app.timezone') }}</code></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
