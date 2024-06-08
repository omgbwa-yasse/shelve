@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Transactions</div>
                    <div class="card-body">
                        <a href="{{ route('transactions.create') }}" class="btn btn-primary mb-3">Create Transaction</a>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Date creation</th>
                                    <th>Mail id</th>
                                    <th>User send</th>
                                    <th>Organisation send id</th>
                                    <th>User received</th>
                                    <th>Organisation received id</th>
                                    <th>Mail status id</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $transaction)
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
                                            <a href="{{ route('transactions.show', $transaction->id) }}" class="btn btn-secondary btn-sm">Show</a>
                                            <a href="{{ route('transactions.edit', $transaction->id) }}" class="btn btn-primary btn-sm">Edit</a>
                                            <form action="{{ route('transactions.destroy', $transaction->id) }}" method="post" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
