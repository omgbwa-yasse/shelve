@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1><i class="bi bi-arrow-counterclockwise"></i> Demande de réactivation</h1>
        <p class="lead">{{ $record->code }} : {{ $record->name }} — statut actuel : <strong>{{ $record->status->name ?? '' }}</strong></p>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('record-reactivations.store', $record) }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Motif de la réactivation</label>
                <textarea name="reason" class="form-control" rows="3" required>{{ old('reason') }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Nouvelle date de transfert prévue (optionnel)</label>
                <input type="date" name="new_transfer_date" class="form-control" value="{{ old('new_transfer_date') }}">
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Envoyer la demande</button>
            <a href="{{ route('record-reactivations.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </form>
    </div>
@endsection
