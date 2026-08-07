@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-pencil-square"></i> {{ $replyTo ? 'Répondre' : 'Nouveau message' }}</h1>
        <a href="{{ route('mails.email.inbox') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @if(!$account)
        <div class="alert alert-info">
            Aucun compte de messagerie actif. <a href="{{ route('settings.email-accounts.create') }}">Configurer un compte</a>.
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('mails.email.send') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        @if($accounts->count() > 1)
                            <div class="col-md-4">
                                <label class="form-label">Compte expéditeur</label>
                                <select name="email_account_id" class="form-select">
                                    @foreach($accounts as $a)
                                        <option value="{{ $a->id }}" @selected($a->id == $account->id)>{{ $a->name }} &lt;{{ $a->email_address }}&gt;</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="email_account_id" value="{{ $account->id }}">
                        @endif

                        @if($replyTo)
                            <input type="hidden" name="in_reply_to" value="{{ $replyTo->message_id }}">
                        @endif

                        <div class="col-12">
                            <label class="form-label">À <span class="text-danger">*</span></label>
                            <input type="text" name="to" value="{{ old('to', $replyTo?->from_address) }}" class="form-control" placeholder="adresse@exemple.com, autre@exemple.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cc</label>
                            <input type="text" name="cc" value="{{ old('cc') }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cci</label>
                            <input type="text" name="bcc" value="{{ old('bcc') }}" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Sujet <span class="text-danger">*</span></label>
                            <input type="text" name="subject" value="{{ old('subject', $replyTo ? 'Re: '.$replyTo->subject : '') }}" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea name="body_html" class="form-control" rows="10" required>{{ old('body_html') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Pièces jointes</label>
                            <input type="file" name="attachments[]" class="form-control" multiple>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button class="btn btn-primary"><i class="bi bi-send"></i> Envoyer</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
