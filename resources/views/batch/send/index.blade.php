@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Parapheurs envoyés</h1>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <table class="table table-bordered table-striped">
            <thead class="">
            <tr>
                <th>Code</th>
                <th>Intitulé</th>
                <th>Organisation de départ</th>
                <th>Organisation d'arrivée</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($batchTransactions as $batchTransaction)
                <tr>
                    <td>{{ $batchTransaction->batch->code }}</td>
                    <td>{{ $batchTransaction->batch->name }}</td>
                    <td>{{ $batchTransaction->organisationSend->name }}</td>
                    <td>{{ $batchTransaction->organisationReceived->name }}</td>
                    <td>{{ $batchTransaction->created_at->format('d/m/Y H:i') }}</td>
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

        {{ $batchTransactions->links() }}
    </div>
@endsection
