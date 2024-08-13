@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Créer Courrier Sortant</h1>

    <form action="{{ route('mail-received.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="code">Code</label>
            <input type="text" name="code" id="code" class="form-control" value="{{ old('code') }}">
        </div>
<div class="form-group">
    <label for="mailInput">Mail</label>
    <input type="text" id="mailInput" class="form-control" />
    <div id="suggestions" class="list-group"></div>
    <input type="hidden" name="mail_id" id="selectedMailId">
</div>
        <div class="form-group">
            <label for="organisation_received_id">Organisation de reception</label>
            <select name="organisation_received_id" id="organisation_received_id" class="form-control">
                @foreach($organisations as $organisation)
                    <option value="{{ $organisation->id }}" {{ old('organisation_received_id') == $organisation->id ? 'selected' : '' }}>
                        {{ $organisation->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="user_received_id">Utilisateur de reception</label>
            <select name="user_received_id" id="user_received_id" class="form-control">
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('user_received_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="document_type_id">Utilisateur d'envoi</label>
            <select name="document_type_id" id="document_type_id" class="form-control">
                @foreach($documentTypes as $documentType)
                    <option value="{{ $documentType->id }}" {{ old('document_type_id') == $documentType->id ? 'selected' : '' }}>
                        {{ $documentType->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="action_id">Action</label>
            <select name="action_id" id="action_id" class="form-control">
                @foreach($mailActions as $action)
                    <option value="{{ $action->id }}" {{ old('action_id') == $action->id ? 'selected' : '' }}>
                        {{ $action->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <input type="text" name="description" id="description" class="form-control" value="{{ old('description') }}">
        </div>


        <input name="user_received_id" id="user_received_id" value="{{ auth()->id() }}" hidden>
        <button type="submit" class="btn btn-primary">Créer</button>
    </form>
</div>

<script>
const mailInput = document.getElementById('mailInput');
const suggestionsContainer = document.getElementById('suggestions');
const selectedMailIdInput = document.getElementById('selectedMailId');


const mails = @json($mails);


mailInput.addEventListener('input', () => {
    const searchTerm = mailInput.value.toLowerCase();
    const filteredMails = mails.filter(mail =>
        mail.code.toLowerCase().includes(searchTerm) ||
        mail.name.toLowerCase().includes(searchTerm)
    );

    suggestionsContainer.innerHTML = '';

    filteredMails.forEach(mail => {
        const suggestionItem = document.createElement('div');
        suggestionItem.classList.add('list-group-item', 'list-group-item-action');
        suggestionItem.textContent = `${mail.code} : ${mail.name}`;

        suggestionItem.addEventListener('click', () => {
            mailInput.value = `${mail.code} : ${mail.name}`;
            selectedMailIdInput.value = mail.id;
            suggestionsContainer.innerHTML = '';
        });

        suggestionsContainer.appendChild(suggestionItem);
    });
});
</script>

@endsection
