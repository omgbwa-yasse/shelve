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
</style>
@section('content')
    <div class="container mt-5">
        <div class="">
            <div class="">
                <h1 class="">{{ $mail->name }}</h1>
            </div>
            <div class="">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Details</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <strong>ID:</strong> {{ $mail->id }}
                            </li>
                            <li class="list-group-item">
                                <strong>Code:</strong> {{ $mail->code }}
                            </li>
                            <li class="list-group-item">
                                <strong>Author(s):</strong>
                                @foreach($mail->authors as $author)
                                    {{ $author->name }}@if(!$loop->last), @endif
                                @endforeach
                            </li>
                            <li class="list-group-item">
                                <strong>Description:</strong> {{ $mail->description }}
                            </li>
                            <li class="list-group-item">
                                <strong>Date:</strong> {{ $mail->date }}
                            </li>
                            <li class="list-group-item">
                                <strong>Priority:</strong> {{ $mail->priority ? $mail->priority->name : 'N/A' }}
                            </li>
                            <li class="list-group-item">
                                <strong>Mail Type:</strong> {{ $mail->type ? $mail->type->name : 'N/A' }}
                            </li>
                            <li class="list-group-item">
                                <strong>Business Type:</strong> {{ $mail->typology ? $mail->typology->name : 'N/A' }}
                            </li>
                            <li class="list-group-item">
                                <strong>Nature:</strong> {{ $mail->documentType ? $mail->documentType->name : 'N/A' }}
                            </li>
                        </ul>
                    </div>
                </div>
                <div class=" justify-content-start">
                    <a href="{{ route('mails.index') }}" class="btn btn-secondary mr-2">Back</a>
                    <a href="{{ route('mails.edit', $mail->id) }}" class="btn btn-warning mr-2">Edit</a>
                    <form action="{{ route('mails.destroy', $mail->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                    <a href="{{ route('mail-attachment.create', ['file' => $mail]) }}" class="btn btn-warning btn-secondary">Ajouter une pièce jointe</a>                </div>
                <hr>
                <h3>Transactions</h3>
                @if($mail->transactions)
                    <div class="timeline">
                        @foreach($mail->transactions as $transaction)
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <h6>{{ $transaction->code }}</h6>
                                    <p>
                                        <strong>Created:</strong> {{ $transaction->date_creation }}<br>
                                        <strong>Sender:</strong> {{ $transaction->organisationSend ? $transaction->organisationSend->name : 'N/A' }} ({{ $transaction->userSend ? $transaction->userSend->name : 'N/A' }})<br>
                                        <strong>Recipient:</strong> {{ $transaction->organisationReceived ? $transaction->organisationReceived->name : 'N/A' }} ({{ $transaction->userReceived ? $transaction->userReceived->name : 'N/A' }})<br>
                                        <strong>Created:</strong> {{ $transaction->created_at }}<br>
                                        <strong>Updated:</strong> {{ $transaction->updated_at }}<br>
                                        <strong>Mail Type:</strong> {{ $transaction->type ? $transaction->type->name : 'N/A' }}<br>
                                        <strong>Document Type:</strong> {{ $transaction->documentType ? $transaction->documentType->name : 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info">No transactions.</div>
                @endif
            </div>

        </div>
    </div>


@endsection
