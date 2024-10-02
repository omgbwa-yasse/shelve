@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Batch Transaction Details</h1>

        <div class="card">
            <div class="card-body">
                <table class="table table-bordered">
                    <tbody>
                    <tr>
                        <th>Batch ID</th>
                        <td>{{ $batchTransaction->batch_id }}</td>
                    </tr>
                    <tr>
                        <th>Organisation Send</th>
                        <td>{{ $batchTransaction->organisationSend->name }}</td>
                    </tr>
                    <tr>
                        <th>Organisation Received</th>
                        <td>{{ $batchTransaction->organisationReceived->name }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            <a href="{{ route('batch-received.index') }}" class="btn btn-secondary">Back</a>
            <a href="{{ route('batch-received.edit', $batchTransaction) }}" class="btn btn-primary">Edit</a>
            <form action="{{ route('batch-received.destroy', $batchTransaction) }}" method="POST" style="display: inline-block;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this batch transaction?')">Delete</button>
            </form>
        </div>
    </div>
@endsection
