@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Transferring slip Details</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ $slip->name }}</h5>
                <p class="card-text">{{ $slip->description }}</p>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Code:</strong> {{ $slip->code }}</li>
                    <li class="list-group-item"><strong>Officer Organisation:</strong> {{ $slip->officerOrganisation->name }}</li>
                    <li class="list-group-item"><strong>Officer:</strong> {{ $slip->officer->name }}</li>
                    <li class="list-group-item"><strong>User Organisation:</strong> {{ $slip->userOrganisation->name }}</li>
                    <li class="list-group-item"><strong>User:</strong> {{ $slip->user ? $slip->user->name : 'None' }}</li>
                    <li class="list-group-item"><strong>Transferring Status:</strong> {{ $slip->slipStatus->name }}</li>
                    <li class="list-group-item"><strong>Is Received:</strong> {{ $slip->is_received ? 'Yes' : 'No' }}</li>
                    <li class="list-group-item"><strong>Received Date:</strong> {{ $slip->received_date ? $slip->received_date->format('Y-m-d H:i:s') : 'None' }}</li>
                    <li class="list-group-item"><strong>Is Approved:</strong> {{ $slip->is_approved ? 'Yes' : 'No' }}</li>
                    <li class="list-group-item"><strong>Approved Date:</strong> {{ $slip->approved_date ? $slip->approved_date->format('Y-m-d H:i:s') : 'None' }}</li>
                </ul>
                <a href="{{ route('slips.index') }}" class="btn btn-secondary mt-3">Back</a>
                <a href="{{ route('slips.edit', $slip->id) }}" class="btn btn-warning mt-3">Edit</a>
                <form action="{{ route('slips.destroy', $slip->id) }}" method="POST" style="display: inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger mt-3" onclick="return confirm('Are you sure you want to delete this slip?')">Delete</button>
                </form>
                <hr>
                <a href="{{ route('slips.records.create', $slip) }}" class="btn btn-warning mt-3">Ajouter des documents</a>

        </div>
        </div>
    </div>
@endsection
