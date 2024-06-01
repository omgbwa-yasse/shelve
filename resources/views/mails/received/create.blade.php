@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Recevoir un mail') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('mails.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type_id" value="{{ $mailTypeId }}">

                        </div>

                        <div class="mb-3">
                            <label for="document" class="form-label">{{ __('Document (optionnel)') }}</label>
                            <input type="file" class="form-control @error('document') is-invalid @enderror" id="document" name="document">
                            @error('document')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="mail_priority_id" class="form-label">{{ __('Priorité') }}</label>
                            <select class="form-select @error('mail_priority_id') is-invalid @enderror" id="mail_priority_id" name="mail_priority_id" required>
                                @foreach ($mailPriorities as $priority)
                                    <option value="{{ $priority->id }}" {{ old('mail_priority_id') == $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
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
                                    <option value="{{ $typology->id }}" {{ old('mail_typology_id') == $typology->id ? 'selected' : '' }}>{{ $typology->name }}</option>
                                @endforeach
                            </select>
                            @error('mail_typology_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">{{ __('Enregistrer') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
