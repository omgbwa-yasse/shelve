@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Create Transaction</div>
                    <div class="card-body">
                        <form action="{{ route('transactions.store') }}" method="post">
                            @csrf
                            <div class="form-group">
                                <label for="code">Code</label>
                                <input type="number" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}">
                                @error('code')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="date_creation">Date creation</label>
                                <input type="datetime-local" name="date_creation" id="date_creation" class="form-control @error('date_creation') is-invalid @enderror" value="{{ old('date_creation') }}">
                                @error('date_creation')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="mail_id">Mail id</label>
                                <input type="number" name="mail_id" id="mail_id" class="form-control @error('mail_id') is-invalid @enderror" value="{{ old('mail_id') }}">
                                @error('mail_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="user_send">User send</label>
                                <input type="number" name="user_send" id="user_send" class="form-control @error('user_send') is-invalid @enderror" value="{{ old('user_send') }}">
                                @error('user_send')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="organisation_send_id">Organisation send id</label>
                                <input type="number" name="organisation_send_id" id="organisation_send_id" class="form-control @error('organisation_send_id') is-invalid @enderror" value="{{ old('organisation_send_id') }}">
                                @error('organisation_send_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="user_received">User received</label>
                                <input type="number" name="user_received" id="user_received" class="form-control @error('user_received') is-invalid @enderror" value="{{ old('user_received') }}">
                                @error('user_received')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="organisation_received_id">Organisation received id</label>
                                <input type="number" name="organisation_received_id" id="organisation_received_id" class="form-control @error('organisation_received_id') is-invalid @enderror" value="{{ old('organisation_received_id') }}">
                                @error('organisation_received_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="mail_status_id">Mail status id</label>
                                <input type="number" name="mail_status_id" id="mail_status_id" class="form-control @error('mail_status_id') is-invalid @enderror" value="{{ old('mail_status_id') }}">
                                @error('mail_status_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">Create</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
