@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Modifier Courrier sortant externe</h1>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('mails.send.external.update', $mail->id) }}" method="POST" class="needs-validation" enctype="multipart/form-data" novalidate>
            @csrf
            @method('PUT')

            <h5 class="card-title mb-4">Informations générales</h5>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="code" class="form-label">Code du courrier</label>
                    <input type="text" id="code" name="code" class="form-control" value="{{ old('code', $mail->code) }}" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="date" class="form-label">Date du courrier</label>
                    <input type="date" id="date" name="date" class="form-control" value="{{ old('date', $mail->date) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="typology_id" class="form-label">Typologie</label>
                    <select name="typology_id" id="typology_id" class="form-select" required>
                        <option value="">Choisir une typologie</option>
                        @foreach($typologies as $typology)
                            <option value="{{ $typology->id }}" {{ old('typology_id', $mail->typology_id) == $typology->id ? 'selected' : '' }}>
                                {{ $typology->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="document_type" class="form-label">Type de document</label>
                    <select name="document_type" id="document_type" class="form-select" required>
                        <option value="">Choisir le type de document</option>
                        <option value="original" {{ old('document_type', $mail->document_type) == 'original' ? 'selected' : '' }}>Original</option>
                        <option value="duplicate" {{ old('document_type', $mail->document_type) == 'duplicate' ? 'selected' : '' }}>Duplicata</option>
                        <option value="copy" {{ old('document_type', $mail->document_type) == 'copy' ? 'selected' : '' }}>Copie</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8 mb-3">
                    <label for="name" class="form-label">Nom/Objet du courrier</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $mail->name) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="priority_id" class="form-label">Priorité</label>
                    <select name="priority_id" id="priority_id" class="form-select">
                        <option value="">Aucune priorité</option>
                        @foreach($priorities as $priority)
                            <option value="{{ $priority->id }}" {{ old('priority_id', $mail->priority_id) == $priority->id ? 'selected' : '' }}>
                                {{ $priority->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3">{{ old('description', $mail->description) }}</textarea>
            </div>

            <h5 class="card-title mb-4 mt-4">Destinataire externe</h5>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="recipient_type" class="form-label">Type de destinataire</label>
                    <select name="recipient_type" id="recipient_type" class="form-select" required>
                        <option value="">Choisir le type de destinataire</option>
                        <option value="external_contact" {{ old('recipient_type', $mail->recipient_type) == 'external_contact' ? 'selected' : '' }}>Contact externe</option>
                        <option value="external_organization" {{ old('recipient_type', $mail->recipient_type) == 'external_organization' ? 'selected' : '' }}>Organisation externe</option>
                    </select>
                </div>
            </div>

            <!-- Section Contact externe -->
            <div id="external-contact-section" style="display: none;">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="external_recipient_id" class="form-label">Contact externe</label>
                        <select name="external_recipient_id" id="external_recipient_id" class="form-select">
                            <option value="">Sélectionner un contact externe</option>
                            @foreach($externalContacts as $contact)
                                <option value="{{ $contact->id }}" {{ old('external_recipient_id', $mail->external_recipient_id) == $contact->id ? 'selected' : '' }}>
                                    {{ $contact->first_name }} {{ $contact->last_name }}
                                    @if($contact->organization)
                                        ({{ $contact->organization->name }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section Organisation externe -->
            <div id="external-organization-section" style="display: none;">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="external_recipient_organization_id" class="form-label">Organisation externe</label>
                        <select name="external_recipient_organization_id" id="external_recipient_organization_id" class="form-select">
                            <option value="">Sélectionner une organisation externe</option>
                            @foreach($externalOrganizations as $organization)
                                <option value="{{ $organization->id }}" {{ old('external_recipient_organization_id', $mail->external_recipient_organization_id) == $organization->id ? 'selected' : '' }}>
                                    {{ $organization->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('mails.send.external.show', $mail->id) }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
                <button type="submit" class="btn btn-warning">
                    <i class="bi bi-save"></i> Mettre à jour
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const recipientTypeSelect = document.getElementById('recipient_type');
            const externalContactSection = document.getElementById('external-contact-section');
            const externalOrganizationSection = document.getElementById('external-organization-section');

            function toggleRecipientSections() {
                const selectedType = recipientTypeSelect.value;

                // Masquer toutes les sections
                externalContactSection.style.display = 'none';
                externalOrganizationSection.style.display = 'none';

                // Afficher la section appropriée
                if (selectedType === 'external_contact') {
                    externalContactSection.style.display = 'block';
                } else if (selectedType === 'external_organization') {
                    externalOrganizationSection.style.display = 'block';
                }
            }

            recipientTypeSelect.addEventListener('change', toggleRecipientSections);

            // Déclencher l'affichage initial
            toggleRecipientSections();
        });
    </script>
@endpush
@endsection
