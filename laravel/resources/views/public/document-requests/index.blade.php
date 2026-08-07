@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Demandes de documents</h2>
                    <a href="{{ route('public.document-requests.create') }}" class="btn btn-primary">Nouvelle demande</a>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Référence</th>
                                    <th>Type de document</th>
                                    <th>Demandeur</th>
                                    <th>Date de demande</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($documentRequests as $request)
                                    <tr>
                                        <td>{{ $request->reference }}</td>
                                        <td>{{ $request->document_type }}</td>
                                        <td>{{ $request->user->name }}</td>
                                        <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @switch($request->status)
                                                @case('pending')
                                                    <span class="badge bg-warning">En attente</span>
                                                    @break
                                                @case('processing')
                                                    <span class="badge bg-info">En traitement</span>
                                                    @break
                                                @case('completed')
                                                    <span class="badge bg-success">Complétée</span>
                                                    @break
                                                @case('rejected')
                                                    <span class="badge bg-danger">Rejetée</span>
                                                    @break
                                            @endswitch
                                        </td>
                                        <td>
                                            <a href="{{ route('public.document-requests.show', $request) }}" class="btn btn-info btn-sm">Voir</a>
                                            @if($request->status === 'pending')
                                                <a href="{{ route('public.document-requests.edit', $request) }}" class="btn btn-warning btn-sm">Modifier</a>
                                                <form action="{{ route('public.document-requests.destroy', $request) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette demande ?')">Annuler</button>
                                                </form>
                                            @endif
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
</div>
@endsection
