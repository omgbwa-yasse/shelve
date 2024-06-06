@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Créer courrier sortant</h1>
    <form action="{{ route('mail-send.store') }}" method="POST">
        @csrf
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">{{ __('Create Transaction') }}</div>

                        <div class="card-body">
                            <form method="POST" action="{{ route('mail-send.store') }}">
                                @csrf

                                <div class="form-group row">
                                    <label for="code" class="col-md-4 col-form-label text-md-right">{{ __('Code') }}</label>

                                    <div class="col-md-6">
                                        <input id="code" type="number" class="form-control @error('code') is-invalid @enderror" name="code" value="{{ old('code') }}" required autocomplete="code" autofocus>

                                        @error('code')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="date_creation" class="col-md-4 col-form-label text-md-right">{{ __('Date Creation') }}</label>

                                    <div class="col-md-6">
                                        <input id="date_creation" type="datetime-local" class="form-control @error('date_creation') is-invalid @enderror" name="date_creation" value="{{ old('date_creation') }}" required autocomplete="date_creation">

                                        @error('date_creation')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="mail_id" class="col-md-4 col-form-label text-md-right">{{ __('Mail ID') }}</label>

                                    <div class="col-md-6">
                                        <input id="mail_id" type="number" class="form-control @error('mail_id') is-invalid @enderror" name="mail_id" value="{{ old('mail_id') }}" required autocomplete="mail_id">

                                        @error('mail_id')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="user_send" class="col-md-4 col-form-label text-md-right">{{ __('User Send') }}</label>

                                    <div class="col-md-6">
                                        <input id="user_send" type="number" class="form-control @error('user_send') is-invalid @enderror" name="user_send" value="{{ old('user_send') }}" required autocomplete="user_send">

                                        @error('user_send')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="organisation_send_id" class="col-md-4 col-form-label text-md-right">{{ __('Organisation Send ID') }}</label>

                                    <div class="col-md-6">
                                        <input id="organisation_send_id" type="number" class="form-control @error('organisation_send_id') is-invalid @enderror" name="organisation_send_id" value="{{ old('organisation_send_id') }}" required autocomplete="organisation_send_id">

                                        @error('organisation_send_id')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="user_received" class="col-md-4 col-form-label text-md-right">{{ __('User Received') }}</label>

                                    <div class="col-md-6">
                                        <input id="user_received" type="number" class="form-control @error('user_received') is-invalid @enderror" name="user_received" value="{{ old('user_received') }}" autocomplete="user_received">

                                        @error('user_received')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="organisation_received_id" class="col-md-4 col-form-label text-md-right">{{ __('Organisation Received ID') }}</label>

                                    <div class="col-md-6">
                                        <input id="organisation_received_id" type="number" class="form-control @error('organisation_received_id') is-invalid @enderror" name="organisation_received_id" value="{{ old('organisation_received_id') }}" autocomplete="organisation_received_id">

                                        @error('organisation_received_id')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="mail_status_id" class="col-md-4 col-form-label text-md-right">{{ __('Mail Status ID') }}</label>

                                    <div class="col-md-6">
                                        <input id="mail_status_id" type="number" class="form-control @error('mail_status_id') is-invalid @enderror" name="mail_status_id" value="{{ old('mail_status_id') }}" required autocomplete="mail_status_id">

                                        @error('mail_status_id')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row mb-0">
                                    <div class="col-md-6 offset-md-4">
                                        <button type="submit" class="btn btn-primary">
                                            {{ __('Create') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</div>
@endsection
