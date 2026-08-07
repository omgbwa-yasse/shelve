@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Demande de document #{{ $documentRequest->reference }}</h2>
                    <div>
                        @if($documentRequest->status === 'pending')
                            <a href="{{ route('public.document-requests.edit', $documentRequest) }}" class="btn btn-warning btn-sm">Modifier</a>
                            <form action="{{ route('public.document-requests.destroy', $documentRequest) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette demande ?')">Annuler</button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <div class="mb-4">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Type de document:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $documentRequest->document_type }}
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Statut:</strong>
                            </div>
                            <div class="col-md-8">
                                @switch($documentRequest->status)
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
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Date de demande:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $documentRequest->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Date souhaitée:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $documentRequest->requested_date->format('d/m/Y') }}
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Description:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $documentRequest->description }}
                            </div>
                        </div>

                        @if($documentRequest->additional_info)
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong>Informations complémentaires:</strong>
                                </div>
                                <div class="col-md-8">
                                    {{ $documentRequest->additional_info }}
                                </div>
                            </div>
                        @endif

                        @if($documentRequest->attachments->count() > 0)
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong>Pièces jointes:</strong>
                                </div>
                                <div class="col-md-8">
                                    <ul class="list-unstyled">
                                        @foreach($documentRequest->attachments as $attachment)
                                            <li>
                                                <a href="{{ route('public.response-attachments.download', $attachment) }}" target="_blank">
                                                    {{ $attachment->original_name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        @if($documentRequest->response)
                            <div class="card mt-4">
                                <div class="card-header">
                                    <h5>Réponse</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <strong>Message:</strong>
                                        <p>{{ $documentRequest->response->message }}</p>
                                    </div>
                                    @if($documentRequest->response->attachments->count() > 0)
                                        <div>
                                            <strong>Documents fournis:</strong>
                                            <ul class="list-unstyled">
                                                @foreach($documentRequest->response->attachments as $attachment)
                                                    <li>
                                                        <a href="{{ route('public.response-attachments.download', $attachment) }}" target="_blank">
                                                            {{ $attachment->original_name }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('public.document-requests.index') }}" class="btn btn-secondary">Retour à la liste</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
