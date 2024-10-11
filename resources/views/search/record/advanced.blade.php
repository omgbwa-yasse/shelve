@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <!-- Colonne de champs principaux -->
        <div class="col-md-3">
            <h5>Champs disponibles</h5>
            <ul class="list-group" id="fields-list">
                <h6 class="mt-3 mb-2">Description</h6>
                <li class="list-group-item" data-field="code" name-field="Code">Code</li>
                <li class="list-group-item" data-field="name" name-field="Intitulé">Nom</li>
                <li class="list-group-item" data-field="author" name-field="Producteur">Auteur</li>
                <li class="list-group-item" data-field="content" name-field="Contenu">Contenu</li>
                <li class="list-group-item" data-field="date_start" name-field="Date début">Date de début</li>
                <li class="list-group-item" data-field="date_end" name-field="Date fin">Date de fin</li>
                <li class="list-group-item" data-field="date_exact" name-field="Date exacte">Date exacte</li>
                <li class="list-group-item" data-field="status" name-field="Statut">Statut</li>
                <li class="list-group-item" data-field="date_creation" name-field="Date création">Date de création</li>

                <h6 class="mt-4 mb-2">Cycle de vie</h6>
                <li class="list-group-item" data-field="dua" name-field="Délai communicabilité">Durée de communicabilité</li>
                <li class="list-group-item" data-field="dul" name-field="Délai légal">Durée légale</li>

                <h6 class="mt-4 mb-2">Localisation</h6>
                <li class="list-group-item" data-field="container" name-field="Boite/chrono">Boite d’archives</li>
                <li class="list-group-item" data-field="shelf" name-field="Etagère">Étagère</li>
                <li class="list-group-item" data-field="room" name-field="Dépôt">Dépôt</li>

                <h6 class="mt-4 mb-2">Indexation</h6>
                <li class="list-group-item" data-field="term" name-field="Terme (thésaurus)">Terme</li>
                <li class="list-group-item" data-field="activity" name-field="Activité">Activité</li>
            </ul>
        </div>

        <!-- Colonne de champs de recherche dynamique -->
        <div class="col-md-9">
            <h5>Critères de recherche</h5>
            <form id="advanced-search-form" method="POST" action="{{ route('records.advanced')}}">
                @csrf
                <div id="search-criteria-container"></div>
                <button type="submit" class="btn btn-primary mt-3">Rechercher</button>
                <button type="button" class="btn btn-secondary mt-3" id="save-search-btn">Enregistrer la recherche</button>
            </form>
        </div>
    </div>
</div>

<!-- Template de critère de recherche -->
<template id="search-criteria-template">
    <div class="search-criteria-row d-flex align-items-center mb-2">
        <input type="hidden" name="field[]" class="field-name">
        <div class="me-2">
            <label class="form-label field-label"></label>
        </div>
        <select class="form-select me-2 field-operator" name="operator[]">
            <!-- Options seront dynamiquement ajoutées en fonction du champ -->
        </select>
        <input type="text" class="form-control me-2 field-value" name="value[]">
        <button type="button" class="btn btn-danger btn-sm remove-criteria-btn">Retirer</button>
    </div>
</template>

<script>
    const data = @json($data);

    document.addEventListener('DOMContentLoaded', function () {
        const fieldsList = document.getElementById('fields-list');
        const searchCriteriaContainer = document.getElementById('search-criteria-container');
        const searchCriteriaTemplate = document.getElementById('search-criteria-template');

        fieldsList.addEventListener('click', function (e) {
            if (e.target && e.target.nodeName === 'LI') {
                const fieldName = e.target.getAttribute('data-field');
                const name = e.target.getAttribute('name-field');
                addSearchCriteria(fieldName, name);
            }
        });

        function addSearchCriteria(field, Name) {
            const criteriaClone = searchCriteriaTemplate.content.cloneNode(true);
            const fieldNameInput = criteriaClone.querySelector('.field-name');
            const fieldLabel = criteriaClone.querySelector('.field-label');
            const operatorSelect = criteriaClone.querySelector('.field-operator');

            fieldNameInput.value = field;


            fieldLabel.textContent = Name.charAt(0).toUpperCase() + Name.slice(1);

            // Définir les options de tri en fonction du champ
            let operators = [];
            switch (field) {
                case 'code':
                case 'name':
                case 'content':
                    operators = ['commence par', 'contient', 'ne contient pas'];
                    break;
                case 'date_start':
                case 'date_end':
                case 'date_exact':
                case 'date_creation':
                case 'dua':
                case 'dul':
                    operators = ['=', '>', '<'];
                    break;
                case 'container':
                case 'shelf':
                case 'room':
                case 'author':
                case 'activity':
                case 'organisation':
                case 'sort':
                case 'status':
                case 'term':
                    operators = ['avec','sauf'];
                    break;

            }

            // Ajouter les options au select
            operators.forEach(op => {
                const option = document.createElement('option');
                option.value = op;
                option.textContent = op;
                operatorSelect.appendChild(option);
            });

            searchCriteriaContainer.appendChild(criteriaClone);
        }

        // Supprimer un critère de recherche
        searchCriteriaContainer.addEventListener('click', function (e) {
            if (e.target && e.target.classList.contains('remove-criteria-btn')) {
                e.target.closest('.search-criteria-row').remove();
            }
        });
    });
    </script>
@endsection
