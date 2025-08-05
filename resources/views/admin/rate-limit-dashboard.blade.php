@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1><i class="bi bi-speedometer2"></i> Dashboard Rate Limiting</h1>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Configuration actuelle -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-gear"></i> Configuration actuelle</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Cache par défaut :</strong> {{ config('cache.default') }}</p>
                            <p><strong>Cache rate limiter :</strong> {{ config('cache.limiter') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Votre utilisateur :</strong> {{ Auth::user()->name }} (ID: {{ Auth::id() }})</p>
                            <p><strong>Dernière connexion :</strong> {{ Auth::user()->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistiques de l'utilisateur actuel -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-person-check"></i> Vos statistiques de rate limiting</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>Utilisé / Maximum</th>
                                    <th>Statut</th>
                                    <th>Disponible dans</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($userStats as $action => $stats)
                                    @php
                                        $used = $stats['max'] - $stats['remaining'];
                                        $isBlocked = $stats['available_in'] > 0;
                                        $percentage = $stats['max'] > 0 ? ($used / $stats['max']) * 100 : 0;
                                    @endphp
                                    <tr class="{{ $isBlocked ? 'table-danger' : ($percentage > 80 ? 'table-warning' : '') }}">
                                        <td>
                                            <strong>{{ ucfirst(str_replace('_', ' ', $action)) }}</strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="me-2">{{ $used }}/{{ $stats['max'] }}</span>
                                                <div class="progress flex-grow-1" style="height: 20px;">
                                                    <div class="progress-bar {{ $isBlocked ? 'bg-danger' : ($percentage > 80 ? 'bg-warning' : 'bg-success') }}"
                                                         style="width: {{ $percentage }}%">
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($isBlocked)
                                                <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Bloqué</span>
                                            @elseif($percentage > 80)
                                                <span class="badge bg-warning"><i class="bi bi-exclamation-triangle"></i> Attention</span>
                                            @else
                                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> OK</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($stats['available_in'] > 0)
                                                {{ ceil($stats['available_in'] / 60) }} minute(s)
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($isBlocked)
                                                <form method="POST" action="{{ route('admin.rate-limit.clear') }}" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="action" value="{{ $action }}">
                                                    <input type="hidden" name="user_id" value="{{ Auth::id() }}">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Êtes-vous sûr de vouloir effacer cette limite ?')">
                                                        <i class="bi bi-trash"></i> Effacer
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Informations sur les limites -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-info-circle"></i> Informations sur les limites</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Limites par défaut :</h6>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-chat-dots text-primary"></i> Communications : <strong>10/heure</strong></li>
                                <li><i class="bi bi-calendar-check text-info"></i> Réservations : <strong>15/heure</strong></li>
                                <li><i class="bi bi-search text-secondary"></i> Recherches : <strong>100/heure</strong></li>
                                <li><i class="bi bi-download text-success"></i> Exports : <strong>5/heure</strong></li>
                                <li><i class="bi bi-cloud text-warning"></i> API générale : <strong>1000/heure</strong></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Conseils :</h6>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-lightbulb text-warning"></i> Les limites se réinitialisent automatiquement</li>
                                <li><i class="bi bi-shield-check text-success"></i> Elles protègent contre les abus</li>
                                <li><i class="bi bi-clock text-info"></i> Attendez avant de réessayer si bloqué</li>
                                <li><i class="bi bi-person-gear text-primary"></i> Contactez un admin si problème persistant</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh toutes les 60 secondes
    setInterval(function() {
        window.location.reload();
    }, 60000);
});
</script>
@endpush
