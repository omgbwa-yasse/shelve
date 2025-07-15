@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            @include('submenu.mails')
        </div>
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Courrier reçu externe : {{ $mail->code }}</h1>
                <div>
                    <a href="{{ route('mails.received.external.edit', $mail->id) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Modifier
                    </a>
                    <a href="{{ route('mails.received.external.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Retour
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Informations générales</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Code :</strong></td>
                                <td>{{ $mail->code }}</td>
                            </tr>
                            <tr>
                                <td><strong>Nom :</strong></td>
                                <td>{{ $mail->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Date :</strong></td>
                                <td>{{ $mail->date ? \Carbon\Carbon::parse($mail->date)->format('d/m/Y') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Typologie :</strong></td>
                                <td>{{ $mail->typology->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Type de document :</strong></td>
                                <td>{{ ucfirst($mail->document_type) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Statut :</strong></td>
                                <td>
                                    @php
                                        $statusClass = match($mail->status->value ?? '') {
                                            'transmitted' => 'bg-info',
                                            'received' => 'bg-success',
                                            'processing' => 'bg-warning',
                                            'processed' => 'bg-primary',
                                            'archived' => 'bg-secondary',
                                            default => 'bg-light text-dark'
                                        };
                                        $statusText = match($mail->status->value ?? '') {
                                            'transmitted' => 'Transmis',
                                            'received' => 'Reçu',
                                            'processing' => 'En traitement',
                                            'processed' => 'Traité',
                                            'archived' => 'Archivé',
                                            default => 'Inconnu'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Expéditeur</h5>
                        <table class="table table-borderless">
                            @if($mail->externalSender)
                                <tr>
                                    <td><strong>Type :</strong></td>
                                    <td>Contact externe</td>
                                </tr>
                                <tr>
                                    <td><strong>Contact :</strong></td>
                                    <td>{{ $mail->externalSender->first_name }} {{ $mail->externalSender->last_name }}</td>
                                </tr>
                                @if($mail->externalSender->organization)
                                    <tr>
                                        <td><strong>Organisation :</strong></td>
                                        <td>{{ $mail->externalSender->organization->name }}</td>
                                    </tr>
                                @endif
                            @elseif($mail->externalSenderOrganization)
                                <tr>
                                    <td><strong>Type :</strong></td>
                                    <td>Organisation externe</td>
                                </tr>
                                <tr>
                                    <td><strong>Organisation :</strong></td>
                                    <td>{{ $mail->externalSenderOrganization->name }}</td>
                                </tr>
                            @elseif($mail->senderOrganisation)
                                <tr>
                                    <td><strong>Type :</strong></td>
                                    <td>Organisation</td>
                                </tr>
                                <tr>
                                    <td><strong>Organisation :</strong></td>
                                    <td>{{ $mail->senderOrganisation->name }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>

                @if($mail->description)
                    <div class="mt-4">
                        <h5>Description</h5>
                        <p>{{ $mail->description }}</p>
                    </div>
                @endif

                @if($mail->attachments && $mail->attachments->count() > 0)
                    <div class="mt-4">
                        <h5>Pièces jointes</h5>
                        <div class="list-group">
                            @foreach($mail->attachments as $attachment)
                                <div class="list-group-item">
                                    <i class="bi bi-file-earmark"></i>
                                    <strong>{{ $attachment->name }}</strong>
                                    <span class="text-muted">({{ number_format($attachment->size / 1024, 2) }} KB)</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
