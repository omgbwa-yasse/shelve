@extends('layouts.app')

@section('content')
    <h1>Batch Transactions</h1>
    <a href="{{ route('batch-send.create') }}" class="btn btn-primary mb-3">Create Batch Transaction</a>
    <table class="table">
        <thead>
            <tr>
                <th>Batch ID</th>
                <th>Organisation Send</th>
                <th>Organisation Received</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($batchTransactions as $batchTransaction)
                <tr>
                    <td>{{ $batchTransaction->batch_id }}</td>
                    <td>{{ $batchTransaction->organisationSend->name }}</td>
                    <td>{{ $batchTransaction->organisationReceived->name }}</td>
                    <td>
                        <a href="{{ route('batch-send.show', $batchTransaction) }}" class="btn btn-sm btn-info">Show</a>
                        <a href="{{ route('batch-send.edit', $batchTransaction) }}" class="btn btn-sm btn-primary">Edit</a>
                        <form action="{{ route('batch-send.destroy', $batchTransaction) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this batch transaction?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
