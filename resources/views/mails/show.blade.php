@extends('layouts.app')

<style>
    .timeline {
        position: relative;
        padding: 20px 0;
    }
    .timeline::before {
        content: '';
        position: absolute;
        top: 0;
        left: 50%;
        width: 2px;
        height: 100%;
        background-color: #e6e6e6;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }
    .timeline-dot {
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 12px;
        height: 12px;
        background-color: #007bff;
        border-radius: 50%;
    }
    .timeline-content {
        width: 45%;
        padding: 20px;
        background-color: #f8f9fa;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    .timeline-item:nth-child(even) .timeline-content {
        float: right;
    }
    .mail-details {
        background-color: #f8f9fa;
        border-radius: 8px;

        margin-bottom: 30px;
    }
    .mail-actions {
        margin-top: 20px;
        margin-bottom: 30px;
    }
    .section-title {
        border-bottom: 2px solid #3498db;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }
</style>

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-lg-11 ">
                <h1 class="mb-4 text-center">{{ $mail->name }}</h1>

                <div class="mail-details">
                    <h5 class="section-title">Mail Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>ID:</strong> {{ $mail->id }}</p>
                            <p><strong>Code:</strong> {{ $mail->code }}</p>
                            <p><strong>Author(s):</strong>
                                @foreach($mail->authors as $author)
                                    {{ $author->name }}@if(!$loop->last), @endif
                                @endforeach
                            </p>
                            <p><strong>Date:</strong> {{ $mail->date }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Priority:</strong> {{ $mail->priority ? $mail->priority->name : 'N/A' }}</p>
                            <p><strong>Mail Type:</strong> {{ $mail->type ? $mail->type->name : 'N/A' }}</p>
                            <p><strong>Business Type:</strong> {{ $mail->typology ? $mail->typology->name : 'N/A' }}</p>
                            <p><strong>Nature:</strong> {{ $mail->documentType ? $mail->documentType->name : 'N/A' }}</p>
                        </div>
                    </div>
                    <p><strong>Description:</strong> {{ $mail->description }}</p>
                </div>

                <div class="mail-actions">
                    <a href="{{ route('mails.index') }}" class="btn btn-secondary mr-2">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                    <a href="{{ route('mails.edit', $mail->id) }}" class="btn btn-warning mr-2">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <form action="{{ route('mails.destroy', $mail->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger mr-2">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </form>
                    <a href="{{ route('mail-attachment.create', ['file' => $mail]) }}" class="btn btn-info">
                        <i class="bi bi-paperclip"></i> Add Attachment
                    </a>
                </div>

                <h3 class="section-title">Transactions</h3>
                @if($mail->transactions->isNotEmpty())
                    <div class="timeline">
                        @foreach($mail->transactions as $transaction)
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-3">{{ $transaction->code }}</h6>
                                    <p>
                                        <strong>Created:</strong> {{ $transaction->date_creation }}<br>
                                        <strong>Sender:</strong> {{ $transaction->organisationSend ? $transaction->organisationSend->name : 'N/A' }} ({{ $transaction->userSend ? $transaction->userSend->name : 'N/A' }})<br>
                                        <strong>Recipient:</strong> {{ $transaction->organisationReceived ? $transaction->organisationReceived->name : 'N/A' }} ({{ $transaction->userReceived ? $transaction->userReceived->name : 'N/A' }})<br>
                                        <strong>Mail Type:</strong> {{ $transaction->type ? $transaction->type->name : 'N/A' }}<br>
                                        <strong>Document Type:</strong> {{ $transaction->documentType ? $transaction->documentType->name : 'N/A' }}
                                    </p>
                                    <small class="text-muted">
                                        Created: {{ $transaction->created_at->format('M d, Y H:i') }}<br>
                                        Updated: {{ $transaction->updated_at->format('M d, Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info">No transactions found.</div>
                @endif
            </div>
        </div>
    </div>
@endsection
