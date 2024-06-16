@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Modifier courrier entrant</h1>

    <form action="{{ route('mail-received.update', $mailTransaction) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="code" class="form-label">Code</label>
            <input type="number" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $mailTransaction->code) }}" required>
            @error('code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="date_creation" class="form-label">Date de création</label>
            <input type="datetime-local" class="form-control @error('date_creation') is-invalid @enderror" id="date_creation" name="date_creation" value="{{ old('date_creation', $mailTransaction->date_creation->format('Y-m-d\TH:i')) }}" required>
            @error('date_creation')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="mail_status_id" class="form-label">Statut du courrier</label>
            <select class="form-control @error('mail_status_id') is-invalid @enderror" id="mail_status_id" name="mail_status_id" required>
                @foreach ($mailStatuses as $status)
                    <option value="{{ $status->id }}" {{ old('mail_status_id', $mailTransaction->mail_status_id) == $status->id ? 'selected' : '' }}>
                        {{ $status->name }}
                    </option>
                @endforeach
            </select>
            @error('mail_status_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>
</div>
@endsection
