@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Export des bordereaux de versement</h2>

        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            L'export sera généré pour un seul bordereau avec deux onglets : informations du bordereau et liste des records associés. Seuls les bordereaux émis ou reçus par votre organisation seront traités.
        </div>

        <form action="{{ route('slips.export') }}" method="POST">
            @csrf
            <div class="form-group mb-3">
                <label for="slip_id">Sélectionner un bordereau (optionnel) :</label>
                <select name="slip_id" id="slip_id" class="form-control">
                    <option value="">Premier bordereau trouvé</option>
                    @foreach($slips as $slip)
                        <option value="{{ $slip->id }}">{{ $slip->code }} - {{ $slip->name }}</option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Si aucun bordereau n'est sélectionné, le premier bordereau trouvé sera exporté.</small>
            </div>
            <div class="form-group mb-3">
                <label for="format">Format d'export :</label>
                <select name="format" id="format" class="form-control">
                    <option value="excel">Excel</option>
                    <option value="ead">EAD</option>
                    <option value="seda">SEDA</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-download"></i> Exporter
            </button>
            <a href="{{ route('slips.index') }}" class="btn btn-secondary ms-2">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </form>
    </div>
@endsection
