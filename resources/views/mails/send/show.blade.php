@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Détails du mail') }}</div>

                <div class="card-body">
                    <div class="mb-3">
                        <label for="code" class="form-label">{{ __('Code') }}</label>
                        <p>{{ $mail->code }}</p>
                    </div>

                    <div class="mb-3">
                        <label for="object" class="form-label">{{ __('Objet') }}</label>
                        <p>{{ $mail->object }}</p>
                    </div>

                    <div class="mb-3">
                        <label for="document" class="form-label">{{ __('Document') }}</label>
                        @if ($mail->document)
                            <a href="{{ asset('storage/' . $mail->document) }}" target="_blank">{{ __('Télécharger le document') }}</a>
                        @else
                            <p>{{ __('Aucun document') }}</p>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="mail_priority_id" class="form-label">{{ __('Priorité') }}</label>
                        <p>{{ $mail->mailPriority->name }}</p>
                    </div>

                    <div class="mb-3">
                        <label for="mail_typology_id" class="form-label">{{ __('Typologie') }}</label>
                        <p>{{ $mail->mailTypology->name }}</p>
                    </div>

                    <a href="{{ route('mails.edit', $mail->id) }}" class="btn btn-primary">{{ __('Modifier') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
