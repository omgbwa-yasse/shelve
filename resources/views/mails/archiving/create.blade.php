@extends('layouts.app')

@section('content')
    <h1>Archiver un courrier</h1>

    <form action="{{ route('mail-archiving.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="container_id" class="form-label">Container</label>
            <select class="form-select" name="container_id" id="container_id" required>
                @foreach ($mailContainers as $container)
                    <option value="{{ $container->id }}" {{ old('container_id') == $container->id ? 'selected' : '' }}>
                        {{ $container->code }} - {{ $container->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="mail_id" class="form-label">Mail</label>
            <input type="text" id="mailInput" class="form-control" autocomplete="off">
            <input type="hidden" id="selectedMailId" name="mail_id" value="{{ old('mail_id') }}">
            <div id="suggestions" class="list-group"></div>
        </div>

        <div class="mb-3">
            <label for="document_type_id" class="form-label">Document Type</label>
            <select class="form-select" name="document_type_id" id="document_type_id" required>
                @foreach ($documentTypes as $documentType)
                    <option value="{{ $documentType->id }}" {{ old('document_type_id') == $documentType->id ? 'selected' : '' }}>
                        {{ $documentType->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Archiver</button>
    </form>

    <script>
        const mailInput = document.getElementById('mailInput');
        const suggestionsContainer = document.getElementById('suggestions');
        const selectedMailIdInput = document.getElementById('selectedMailId');

        const mails = @json($mails);

        mailInput.addEventListener('input', () => {
            const searchTerm = mailInput.value.toLowerCase();
            const filteredMails = mails.filter(mail =>
                mail.name.toLowerCase().includes(searchTerm)
            );

            suggestionsContainer.innerHTML = '';

            filteredMails.forEach(mail => {
                const suggestionItem = document.createElement('div');
                suggestionItem.classList.add('list-group-item', 'list-group-item-action');
                suggestionItem.textContent = mail.name;

                suggestionItem.addEventListener('click', () => {
                    mailInput.value = mail.name;
                    selectedMailIdInput.value = mail.id;
                    suggestionsContainer.innerHTML = '';
                });

                suggestionsContainer.appendChild(suggestionItem);
            });
        });
    </script>
@endsection
