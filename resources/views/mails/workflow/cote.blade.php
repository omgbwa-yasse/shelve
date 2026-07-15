@extends('layouts.app')

@section('content')
<div class="container shadow-sm p-4 bg-white rounded">
    <h1 class="mb-4 text-primary">Cotation du courrier</h1>

    <div class="mb-4">
        <strong>Objet :</strong> {{ $mail->name }}<br>
        <strong>Code :</strong> {{ $mail->code }}<br>
        <strong>Date :</strong> {{ optional($mail->date)->format('d/m/Y') }}
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('mails.workflow.cote', $mail->id) }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="assigned_organisation_id" class="form-label">Affecter à la direction</label>
            <select name="assigned_organisation_id" id="assigned_organisation_id" class="form-select" required>
                <option value="">Choisir une direction</option>
                @foreach($organisations as $org)
                    <option value="{{ $org->id }}" {{ old('assigned_organisation_id', $mail->assigned_organisation_id) == $org->id ? 'selected' : '' }}>
                        {{ $org->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="action_id" class="form-label">Instruction du DG</label>
            <select name="action_id" id="action_id" class="form-select">
                <option value="">Aucune instruction</option>
                @foreach($instructions as $instruction)
                    <option value="{{ $instruction->id }}" {{ old('action_id') == $instruction->id ? 'selected' : '' }}>
                        {{ $instruction->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="instruction" class="form-label">Précisions (facultatif)</label>
            <textarea name="instruction" id="instruction" class="form-control" rows="3">{{ old('instruction') }}</textarea>
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('mails.incoming.show', $mail->id) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Retour
            </a>
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check2-circle"></i> Coter le courrier
            </button>
        </div>
    </form>
</div>
@endsection
