@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Liste des Courriers sortants</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Date Creation</th>
                <th>Mail ID</th>
                <th>User Send</th>
                <th>Organisation Send</th>
                <th>User Received</th>
                <th>Organisation Received</th>
                <th>Mail Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transactions as $transaction)
            <tr>
                <td>{{ $transaction->code }}</td>
                <td>{{ $transaction->date_creation }}</td>
                <td>{{ $transaction->mail_id }}</td>
                <td>{{ $transaction->user_send }}</td>
                <td>{{ $transaction->organisation_send_id }}</td>
                <td>{{ $transaction->user_received }}</td>
                <td>{{ $transaction->organisation_received_id }}</td>
                <td>{{ $transaction->mail_status_id }}</td>
                <td>
                    <a href="{{ route('mail-send.show', $transaction->id) }}" class="btn btn-primary">Show</a>
                    <a href="{{ route('mail-send.edit', $transaction->id) }}" class="btn btn-secondary">Edit</a>
                    <form action="{{ route('mail-send.destroy', $transaction->id) }}" method="POST" style="display: inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
