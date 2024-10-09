@extends('layouts.app')

    <style>
        .form-section {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-section h4 {
            margin-bottom: 20px;
            color: #0056b3;
        }
        .btn-floating {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 100;
        }
    </style>

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="">
                <div class="">
                    <div class="card-header bg-primary text-white py-3">
                        <h3 class="mb-0">Créer un courrier</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('mails.store') }}" method="POST">
                            @csrf

                            <div class="form-section">
                                <h4><i class="bi bi-info-circle"></i> Informations générales</h4>
                                <div class="row">
                                    <div class="col-md-6 ">
                                        <label for="code" class="form-label">Code</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-barcode"></i></span>
                                            <input type="text" class="form-control" id="code" name="code" value="{{ old('code') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 ">
                                        <label for="date" class="form-label">Date</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                            <input type="date" class="form-control" id="date" name="date" value="{{ old('date') }}" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="">
                                    <label for="name" class="form-label">Intitulé du courrier</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h4><i class="bi bi-person"></i> Producteur</h4>
                                <div class="">
                                    <label for="authorInput" class="form-label">Sélectionner le producteur</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" id="authorInput" class="form-control" readonly required>
                                        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#authorModal">
                                            <i class="bi bi-search"></i> Sélectionner
                                        </button>
                                    </div>
                                    <input type="hidden" name="author_id" id="selectedAuthorId">
                                </div>
                            </div>

                            <div class="form-section">
                                <h4><i class="bi bi-list-ul"></i> Détails du courrier</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="mail_priority_id" class="form-label">Priorité</label>
                                        <select class="form-select" id="mail_priority_id" name="mail_priority_id" required>
                                            <option value="">Choisir une priorité</option>
                                            @foreach ($priorities as $priority)
                                                <option value="{{ $priority->id }}" {{ old('mail_priority_id') == $priority->id ? 'selected' : '' }}>
                                                    {{ $priority->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 ">
                                        <label for="mail_type_id" class="form-label">Type de courrier</label>
                                        <select class="form-select" id="mail_type_id" name="mail_type_id" required>
                                            <option value="">Choisir un type</option>
                                            @foreach ($types as $type)
                                                <option value="{{ $type->id }}" {{ old('mail_type_id') == $type->id ? 'selected' : '' }}>
                                                    {{ $type->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="mail_typology_id" class="form-label">Typologie</label>
                                        <select class="form-select" id="mail_typology_id" name="mail_typology_id" required>
                                            <option value="">Choisir une typologie</option>
                                            @foreach ($typologies as $typology)
                                                <option value="{{ $typology->id }}" {{ old('mail_typology_id') == $typology->id ? 'selected' : '' }}>
                                                    {{ $typology->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 ">
                                        <label for="document_type_id" class="form-label">Nature</label>
                                        <select class="form-select" id="document_type_id" name="document_type_id" required>
                                            <option value="">Choisir une nature</option>
                                            @foreach ($documentTypes as $documentType)
                                                <option value="{{ $documentType->id }}" {{ old('document_type_id') == $documentType->id ? 'selected' : '' }}>
                                                    {{ $documentType->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h4><i class="bi bi-chat-left-text"></i> Description</h4>
                                <div class="mb-3">
                                    <textarea class="form-control" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg btn-floating">
                                <i class="bi bi-check-lg"></i> Créer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal pour la sélection du producteur --}}
    <div class="modal fade" id="authorModal" tabindex="-1" aria-labelledby="authorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="authorModalLabel">Sélectionner un producteur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="authorSearch" class="form-control mb-3" placeholder="Rechercher un producteur...">
                    <div id="authorList" class="list-group">
                        @foreach($authors as $author)
                            <a href="#" class="list-group-item list-group-item-action" data-id="{{ $author->id }}" data-name="{{ $author->name }}">
                                {{ $author->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const authorModal = document.getElementById('authorModal');
            const authorSearch = document.getElementById('authorSearch');
            const authorList = document.getElementById('authorList');
            const authorItems = authorList.querySelectorAll('.list-group-item');
            const authorInput = document.getElementById('authorInput');
            const selectedAuthorId = document.getElementById('selectedAuthorId');

            function filterAuthors() {
                const filter = authorSearch.value.toLowerCase();
                authorItems.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    item.style.display = text.includes(filter) ? '' : 'none';
                });
            }

            authorSearch.addEventListener('input', filterAuthors);

            authorItems.forEach(item => {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    authorInput.value = item.dataset.name;
                    selectedAuthorId.value = item.dataset.id;
                    bootstrap.Modal.getInstance(authorModal).hide();
                });
            });
        });
    </script>
@endsection




