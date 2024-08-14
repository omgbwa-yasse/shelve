@extends('layouts.app')

@section('content')
    <h1>Batch Transaction Details</h1>
    <table class="table">
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
    <a href="{{ route('batch-send.edit', $batchTransaction) }}" class="btn btn-primary">Edit</a>
    <form action="{{ route('batch-send.destroy', $batchTransaction) }}" method="POST" style="display: inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this batch transaction?')">Delete</button>
    </form>
@endsection
