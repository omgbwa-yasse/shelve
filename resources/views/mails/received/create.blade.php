@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Créer Courrier entrant</h1>
    <!-- resources/views/mail_transactions/create.blade.php -->

<form action="{{ route('mail-received.store') }}" method="POST">
    @csrf
    <div class="form-group">
        <label for="code">Code</label>
        <input type="text" name="code" id="code" class="form-control">
    </div>
    <div class="form-group">
        <label for="mail_id">Mail</label>
        <select name="mail_id" id="mail_id" class="form-control">
            @foreach($mails as $mail)
                <option value="{{ $mail->id }}">{{ $mail->code }} : {{ $mail->name }}, {{ $mail->author }} </option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label for="organisation_send_id">Organisation d'envoi</label>
        <select name="organisation_send_id" id="organisation_send_id" class="form-control">
            @foreach($organisations as $organisation)
                <option value="{{ $organisation->id }}">{{ $organisation->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label for="user_send_id">Utilisateur d'envoi</label>
        <select name="user_send_id" id="user_send_id" class="form-control">
            @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Créer</button>
</form>

@endsection
