@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Courrier sortant externe : {{ $mail->code }}</h1>
            <div>
                <a href="{{ route('mails.send.external.edit', $mail->id) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Modifier
                </a>
                <a href="{{ route('mails.send.external.index') }}" class="btn btn-secondary">
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
                                            'draft' => 'bg-secondary',
                                            'pending' => 'bg-warning',
                                            'approved' => 'bg-success',
                                            'sent' => 'bg-primary',
                                            'rejected' => 'bg-danger',
                                            default => 'bg-light text-dark'
                                        };
                                        $statusText = match($mail->status->value ?? '') {
                                            'draft' => 'Brouillon',
                                            'pending' => 'En attente',
                                            'approved' => 'Approuvé',
                                            'sent' => 'Envoyé',
                                            'rejected' => 'Rejeté',
                                            default => 'Inconnu'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Destinataire</h5>
                        <table class="table table-borderless">
                            @if($mail->externalRecipient)
                                <tr>
                                    <td><strong>Type :</strong></td>
                                    <td>Contact externe</td>
                                </tr>
                                <tr>
                                    <td><strong>Contact :</strong></td>
                                    <td>{{ $mail->externalRecipient->first_name }} {{ $mail->externalRecipient->last_name }}</td>
                                </tr>
                                @if($mail->externalRecipient->organization)
                                    <tr>
                                        <td><strong>Organisation :</strong></td>
                                        <td>{{ $mail->externalRecipient->organization->name }}</td>
                                    </tr>
                                @endif
                            @elseif($mail->externalRecipientOrganization)
                                <tr>
                                    <td><strong>Type :</strong></td>
                                    <td>Organisation externe</td>
                                </tr>
                                <tr>
                                    <td><strong>Organisation :</strong></td>
                                    <td>{{ $mail->externalRecipientOrganization->name }}</td>
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
