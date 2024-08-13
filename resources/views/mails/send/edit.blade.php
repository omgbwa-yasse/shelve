@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Modifier Courrier sortant</h1>
    <form action="{{ route('mail-send.update', $mailTransaction->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="code">Code</label>
            <input type="text" name="code" id="code" class="form-control" value="{{ old('code', $mailTransaction->code) }}">
        </div>
        <div class="form-group">
            <label for="mailInput">Mail</label>
            <input type="text" id="mailInput" class="form-control" value="{{ old('mail_id', $mailTransaction->mail->code . ' : ' . $mailTransaction->mail->name) }}" />
            <div id="suggestions" class="list-group"></div>
            <input type="hidden" name="mail_id" id="selectedMailId" value="{{ old('mail_id', $mailTransaction->mail_id) }}">
        </div>
        <div class="form-group">
            <label for="organisation_received_id">Organisation reçu par</label>
            <select name="organisation_received_id" id="organisation_received_id" class="form-control">
                @foreach($organisations as $organisation)
                    <option value="{{ $organisation->id }}" {{ old('organisation_received_id', $mailTransaction->organisation_received_id) == $organisation->id ? 'selected' : '' }}>
                        {{ $organisation->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="user_received_id">Utilisateur de réception </label>
            <select name="user_received_id" id="user_received_id" class="form-control">
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('user_received_id', $mailTransaction->user_received_id) == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="document_type_id">Nature de la copie</label>
            <select name="document_type_id" id="document_type_id" class="form-control">
                @foreach($documentTypes as $documentType)
                    <option value="{{ $documentType->id }}" {{ old('document_type_id', $mailTransaction->document_type_id) == $documentType->id ? 'selected' : '' }}>
                        {{ $documentType->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="organisation_send_id">Envoyer par</label>
            <select name="organisation_send_id" id="organisation_send_id" class="form-control">
                @foreach($sendOrganisations as $organisation)
                    <option value="{{ $organisation->id }}" {{ old('organisation_send_id', $mailTransaction->organisation_send_id) == $organisation->id ? 'selected' : '' }}>
                        {{ $organisation->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="action_id">Action</label>
            <select name="action_id" id="action_id" class="form-control">
                @foreach($mailActions as $action)
                    <option value="{{ $action->id }}" {{ old('action_id', $mailTransaction->action_id) == $action->id ? 'selected' : '' }}>
                        {{ $action->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <input type="text" name="description" id="description" class="form-control" value="{{ old('description', $mailTransaction->description) }}">
        </div>
        <button type="submit" class="btn btn-primary">Modifier</button>
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
