@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Détails du bordereau du versement</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ $slip->code }} - {{ $slip->name }}</h5>
                <p class="card-text">{{ $slip->description }}</p>

                <div class="container">
                    <div class="row">
                        <div class="col-md-6 service-versant">
                            <table class="table table-borderless">
                                <thead>
                                    <tr>
                                        <th colspan="2">Service versant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>User Organisation:</strong></td>
                                        <td>{{ $slip->userOrganisation->name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>User:</strong></td>
                                        <td>{{ $slip->user ? $slip->user->name : 'None' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="col-md-6 service-archives">
                            <table class="table table-borderless">
                                <thead>
                                    <tr>
                                        <th colspan="2">Service d'archives</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Officer Organisation:</strong></td>
                                        <td>{{ $slip->officerOrganisation->name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Officer:</strong></td>
                                        <td>{{ $slip->officer->name }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="statut-transfert">
                        <table class="table table-borderless">
                            <thead>
                                <tr>
                                    <th colspan="1">Statut du transfert</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Transferring Status:</strong></td>
                                    <td>{{ $slip->slipStatus->name }}</td>
                                    <td><strong>Is Received:</strong></td>
                                    <td>{{ $slip->is_received ? 'Yes' : 'No' }}</td>
                                    <td><strong>Received Date:</strong></td>
                                    <td>{{ $slip->received_date ? $slip->received_date->format('Y-m-d H:i:s') : 'None' }}</td>
                                    <td><strong>Is Approved:</strong></td>
                                    <td>{{ $slip->is_approved ? 'Yes' : 'No' }}</td>
                                    <td><strong>Approved Date:</strong></td>
                                    <td>{{ $slip->approved_date ? $slip->approved_date->format('Y-m-d H:i:s') : 'None' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                </div>
            </div>



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
