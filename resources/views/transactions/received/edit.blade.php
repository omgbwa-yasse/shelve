@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Transaction</h1>
    <form action="{{ route('transactions.update', $transaction->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="code" class="form-label">Code</label>
            <input type="number" class="form-control" id="code" name="code" value="{{ $transaction->code }}" required>
        </div>
        <div class="mb-3">
            <label for="date_creation" class="form-label">Date Creation</label>
            <input type="datetime-local" class="form-control" id="date_creation" name="date_creation" value="{{ $transaction->date_creation->format('Y-m-d\TH:i') }}" required>
        </div>
        <div class="mb-3">
            <label for="mail_id" class="form-label">Mail ID</label>
            <input type="number" class="form-control" id="mail_id" name="mail_id" value="{{ $transaction->mail_id }}" required>
        </div>
        <div class="mb-3">
            <label for="user_send" class="form-label">User Send</label>
            <input type="number" class="form-control" id="user_send" name="user_send" value="{{ $transaction->user_send }}" required>
        </div>
        <div class="mb-3">
            <label for="organisation_send_id" class="form-label">Organisation Send ID</label>
            <input type="number" class="form-control" id="organisation_send_id" name="organisation_send_id" value="{{ $transaction->organisation_send_id }}" required>
        </div>
        <div class="mb-3">
            <label for="user_received" class="form-label">User Received</label>
            <input type="number" class="form-control" id="user_received" name="user_received" value="{{ $transaction->user_received }}">
        </div>
        <div class="mb-3">
            <label for="organisation_received_id" class="form-label">Organisation Received ID</label>
            <input type="number" class="form-control" id="organisation_received_id" name="organisation_received_id" value="{{ $transaction->organisation_received_id }}">
        </div>
        <div class="mb-3">
            <label for="mail_status_id" class="form-label">Mail Status ID</label>
            <input type="number" class="form-control" id="mail_status_id" name="mail_status_id" value="{{ $transaction->mail_status_id }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
