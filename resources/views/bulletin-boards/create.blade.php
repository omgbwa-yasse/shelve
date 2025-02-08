<!-- resources/views/bulletin-boards/create.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>{{ isset($bulletinBoard) ? 'Modifier la publication' : 'Nouvelle publication' }}</h2>
                    <a href="{{ route('bulletin-boards.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Retour
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ isset($bulletinBoard) ? route('bulletin-boards.update', $bulletinBoard) : route('bulletin-boards.store') }}"
                              method="POST"
                              enctype="multipart/form-data">
                            @csrf
                            @if(isset($bulletinBoard))
                                @method('PUT')
                            @endif

                            <div class="mb-3">
                                <label class="form-label required">Type de publication</label>
                                <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">Sélectionnez un type</option>
                                    <option value="post" {{ old('type', $bulletinBoard->type ?? '') == 'post' ? 'selected' : '' }}>
                                        Publication standard
                                    </option>
                                    <option value="event" {{ old('type', $bulletinBoard->type ?? '') == 'event' ? 'selected' : '' }}>
                                        Événement
                                    </option>
                                    <option value="announcement" {{ old('type', $bulletinBoard->type ?? '') == 'announcement' ? 'selected' : '' }}>
                                        Annonce
                                    </option>
                                </select>
                                @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label required">Titre</label>
                                <input type="text"
                                       name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $bulletinBoard->name ?? '') }}"
                                       required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description"
                                          class="form-control @error('description') is-invalid @enderror"
                                          rows="5">{{ old('description', $bulletinBoard->description ?? '') }}</textarea>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div id="event-fields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Date de début</label>
                                            <input type="datetime-local"
                                                   name="start_date"
                                                   class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Date de fin</label>
                                            <input type="datetime-local"
                                                   name="end_date"
                                                   class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Lieu</label>
                                    <input type="text"
                                           name="location"
                                           class="form-control">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Pièces jointes</label>
                                <div class="input-group">
                                    <input type="file"
                                           name="attachments[]"
                                           class="form-control"
                                           multiple>
                                    <button type="button"
                                            class="btn btn-outline-secondary"
                                            id="addMoreFiles">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                                <div id="attachmentsList" class="mt-2">
                                    @if(isset($bulletinBoard) && $bulletinBoard->attachments->count() > 0)
                                        @foreach($bulletinBoard->attachments as $attachment)
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <i class="bi bi-paperclip"></i>
                                                <span>{{ $attachment->name }}</span>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="removeAttachment({{ $attachment->id }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Organisations concernées</label>
                                <select name="organisations[]"
                                        class="form-select"
                                        multiple>
                                    @foreach($organisations as $organisation)
                                        <option value="{{ $organisation->id }}"
                                            {{ isset($bulletinBoard) && $bulletinBoard->organisations->contains($organisation->id) ? 'selected' : '' }}>
                                            {{ $organisation->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="submit"
                                        name="status"
                                        value="draft"
                                        class="btn btn-secondary">
                                    Enregistrer comme brouillon
                                </button>
                                <button type="submit"
                                        name="status"
                                        value="published"
                                        class="btn btn-primary">
                                    Publier
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Options de publication</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Visibilité</label>
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="radio"
                                       name="visibility"
                                       value="public"
                                       checked>
                                <label class="form-check-label">
                                    Public
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="radio"
                                       name="visibility"
                                       value="private">
                                <label class="form-check-label">
                                    Privé (organisations sélectionnées uniquement)
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notification</label>
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="notify_users"
                                       value="1">
                                <label class="form-check-label">
                                    Notifier les utilisateurs concernés
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const typeSelect = document.getElementById('type');
                const eventFields = document.getElementById('event-fields');

                function toggleEventFields() {
                    if (typeSelect.value === 'event') {
                        eventFields.style.display = 'block';
                    } else {
                        eventFields.style.display = 'none';
                    }
                }

                typeSelect.addEventListener('change', toggleEventFields);
                toggleEventFields();

                // Gestion des pièces jointes
                const addMoreFilesBtn = document.getElementById('addMoreFiles');
                const attachmentsList = document.getElementById('attachmentsList');

                addMoreFilesBtn.addEventListener('click', function() {
                    const input = document.createElement('input');
                    input.type = 'file';
                    input.name = 'attachments[]';
                    input.className = 'form-control mt-2';
                    input.multiple = true;
                    attachmentsList.appendChild(input);
                });
            });

            function removeAttachment(attachmentId) {
                if (confirm('Êtes-vous sûr de vouloir supprimer cette pièce jointe ?')) {
                    fetch(`/bulletin-boards/attachments/${attachmentId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    }).then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.querySelector(`[data-attachment-id="${attachmentId}"]`).remove();
                            }
                        });
                }
            }
        </script>
    @endpush
