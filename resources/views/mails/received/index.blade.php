@extends('layouts.app')

@section('content')
<div id="mailList">
    <div class="container">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Courriers entrants</h1>

    <div class="d-flex justify-content-between align-items-center bg-light p-3 mb-3">
        <div class="d-flex align-items-center">
            <a href="#" id="cartBtn" class="btn btn-light btn-sm me-2">
                <i class="bi bi-cart me-1"></i>
                Chariot ***
            </a>
            <a href="#" id="exportBtn" class="btn btn-light btn-sm me-2">
                <i class="bi bi-download me-1"></i>
                Exporter ***
            </a>
            <a href="#" id="printBtn" class="btn btn-light btn-sm me-2">
                <i class="bi bi-printer me-1"></i>
                Imprimer ***
            </a>
        </div>
        <div class="d-flex align-items-center">
            <a href="#" id="transferBtn" class="btn btn-light btn-sm me-2">
                <i class="bi bi-arrow-repeat me-1"></i>
                Transférer ***
            </a>

            <a href="#" id="communicateBtn" class="btn btn-light btn-sm me-2">
                <i class="bi bi-envelope me-1"></i>
                Communiquer ***
            </a>
            <a href="#" id="checkAllBtn" class="btn btn-light btn-sm">
                <i class="bi bi-check-square me-1"></i>
                Tout cocher ***
            </a>
        </div>
    </div>



        <div class="overflow-x-auto bg-white shadow-md rounded-lg">
                @foreach ($transactions as $transaction)
                <h4 class="card-title mb-2">
                    <div class="btn-group mt-1" role="group">
                        <input type="checkbox" class="me-2" name="selected_mail[]" value="{{ $transaction->id }}" id="mail_{{ $transaction->id }}" autocomplete="off" />
                    </div>
                    <a href="{{ route('mails.show', $transaction) }}"><b>{{ $transaction->code ?? 'N/A' }} : {{ $transaction->mail->name ?? 'N/A' }}</b></a>
                    <span class="badge bg-danger }}">
                        {{ $transaction->action->name }}
                    </span>
                </h4>
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-9">
                                <p class="card-text mb-1">
                                        <i class="bi bi-info-circle me-2"></i><em>Description :</em>{{ $transaction->description }}<br/>
                                        <i class="bi bi-person me-2"></i><em>Envoyé par :</em><a href="#"> {{ $transaction->userSend->name ?? 'N/A' }}</a>
                                        <i class="bi bi-building me-2"></i><em>Poste :</em> <a href="#"> {{ $transaction->organisationSend->name ?? 'N/A' }}</a>
                                        <i class="bi bi-file-earmark me-2"></i><em>Type de document :</em> <a href="#"> {{ $transaction->documentType->name ?? 'N/A' }}</a>
                                        <i class="bi bi-calendar me-2"></i><em>le :</em> <a href="#"> {{ $transaction->date_creation ? date('Y-m-d', strtotime($transaction->date_creation)) : 'N/A' }}</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
