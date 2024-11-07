@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Archiver un courrier</h1>
        <form action="{{ route('mail-archive.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="container_id" class="form-label">Container</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="containerInput" readonly required>
                    <input type="hidden" name="container_id" id="container_id" value="{{ old('container_id') }}">
                    <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#containerModal">
                        Sélectionner
                    </button>
                </div>
            </div>

            <div class="mb-3">
                <label for="mail_id" class="form-label">Mail</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="mailInput" readonly required>
                    <input type="hidden" name="mail_id" id="mail_id" value="{{ old('mail_id') }}">
                    <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#mailModal">
                        Sélectionner
                    </button>
                </div>
            </div>

            <div class="mb-3">
                <label for="document_type_id" class="form-label">Type de document</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="documentTypeInput" readonly required>
                    <input type="hidden" name="document_type_id" id="document_type_id" value="{{ old('document_type_id') }}">
                    <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#documentTypeModal">
                        Sélectionner
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Archiver</button>
        </form>
    </div>

    <!-- Container Modal -->
    <div class="modal fade" id="containerModal" tabindex="-1" aria-labelledby="containerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="containerModalLabel">Sélectionner un container</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="containerSearch" class="form-control mb-3" placeholder="Rechercher un container...">
                    <div id="containerList" class="list-group">
                        @foreach ($mailContainers as $container)
                            <button type="button" class="list-group-item list-group-item-action" data-id="{{ $container->id }}" data-name="{{ $container->code }} - {{ $container->name }}">
                                {{ $container->code }} - {{ $container->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mail Modal -->
    <div class="modal fade" id="mailModal" tabindex="-1" aria-labelledby="mailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mailModalLabel">Sélectionner un mail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="mailSearch" class="form-control mb-3" placeholder="Rechercher un mail...">
                    <div id="mailList" class="list-group">
                        @foreach ($mails as $mail)
                            <button type="button" class="list-group-item list-group-item-action" data-id="{{ $mail->id }}" data-name="{{ $mail->name }}">
                                {{ $mail->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Type Modal -->
    <div class="modal fade" id="documentTypeModal" tabindex="-1" aria-labelledby="documentTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="documentTypeModalLabel">Sélectionner un type de document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="documentTypeSearch" class="form-control mb-3" placeholder="Rechercher un type de document...">
                    <div id="documentTypeList" class="list-group">
                        <select name="document_type" id="document_type" class="form-select">
                            <option value="original">Original</option>
                            <option value="copy">Copie</option>
                            <option value="duplicate">Duplicata</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const setupModal = (inputId, hiddenInputId, modalId, searchInputId, listId) => {
                const input = document.getElementById(inputId);
                const hiddenInput = document.getElementById(hiddenInputId);
                const modal = document.getElementById(modalId);
                const searchInput = document.getElementById(searchInputId);
                const list = document.getElementById(listId);

                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    Array.from(list.children).forEach(item => {
                        const text = item.textContent.toLowerCase();
                        item.style.display = text.includes(searchTerm) ? '' : 'none';
                    });
                });

                list.addEventListener('click', function(e) {
                    if (e.target.tagName === 'BUTTON') {
                        input.value = e.target.dataset.name;
                        hiddenInput.value = e.target.dataset.id;
                        bootstrap.Modal.getInstance(modal).hide();
                    }
                });
            };

            setupModal('containerInput', 'container_id', 'containerModal', 'containerSearch', 'containerList');
            setupModal('mailInput', 'mail_id', 'mailModal', 'mailSearch', 'mailList');
            setupModal('documentTypeInput', 'document_type_id', 'documentTypeModal', 'documentTypeSearch', 'documentTypeList');
        });
    </script>
@endsection
