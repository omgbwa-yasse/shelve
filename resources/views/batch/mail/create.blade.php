@extends('layouts.app')

@section('content')
    <h1>Create Batch Mail for {{ e($batch->name) }}</h1>
    <form action="{{ route('batch.mail.store', $batch) }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="mailInput">Mail</label>
            <input type="text" id="mailInput" class="form-control" />
            <div id="suggestions" class="list-group"></div>
            <input type="hidden" name="mail_id" id="selectedMailId">
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>

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
