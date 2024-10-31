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
        </div>
    </div>
@endsection
