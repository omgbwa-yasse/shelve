@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Transaction details</div>
                    <div class="card-body">
                        <p><strong>Code:</strong> {{ $transaction->code }}</p>
                        <p><strong>Date creation:</strong> {{ $transaction->date_creation }}</p>
                        <p><strong>Mail id:</strong> {{ $transaction->mail_id }}</p>
                        <p><strong>User send:</strong> {{ $transaction->user_send }}</p>
                        <p><strong>Organisation send id:</strong> {{ $transaction->organisation_send_id }}</p>
                        <p><strong>User received:</strong> {{ $transaction->user_received }}</p>
                        <p><strong>Organisation received id:</strong> {{ $transaction->organisation_received_id }}</p>
                        <p><strong>Mail status id:</strong> {{ $transaction->mail_status_id }}</p>
                        <a href="{{ route('transactions.index') }}" class="btn btn-secondary">Back to transactions</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
