@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Modifier le mail') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('mails.update', $mail->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="code" class="form-label">{{ __('Code') }}</label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ $mail->code }}" required autofocus>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="object" class="form-label">{{ __('Objet') }}</label>
                            <input type="text" class="form-control @error('object') is-invalid @enderror" id="object" name="object" value="{{ $mail->object }}" required>
                            @error('object')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="document" class="form-label">{{ __('Document (optionnel)') }}</label>
                            <input type="file" class="form-control @error('document') is-invalid @enderror" id="document" name="document">
                            @if ($mail->document)
                                <p>{{ __('Document actuel') }}: <a href="{{ asset('storage/' . $mail->document) }}" target="_blank">{{ $mail->document }}</a></p>
                            @endif
                            @error('document')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="mail_priority_id" class="form-label">{{ __('Priorité') }}</label>
                            <select class="form-select @error('mail_priority_id') is-invalid @enderror" id="mail_priority_id" name="mail_priority_id" required>
                                @foreach ($mailPriorities as $priority)
                                    <option value="{{ $priority->id }}" {{ $priority->id == $mail->mail_priority_id ? 'selected' : '' }}>{{ $priority->name }}</option>
                                @endforeach
                            </select>
                            @error('mail_priority_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="mail_typology_id" class="form-label">{{ __('Typologie') }}</label>
                            <select class="form-select @error('mail_typology_id') is-invalid @enderror" id="mail_typology_id" name="mail_typology_id" required>
                                @foreach ($mailTypologies as $typology)
                                    <option value="{{ $typology->id }}" {{ $typology->id == $mail->mail_typology_id ? 'selected' : '' }}>{{ $typology->name }}</option>
                                @endforeach
                            </select>
                            @error('mail_typology_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">{{ __('Enregistrer les modifications') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
