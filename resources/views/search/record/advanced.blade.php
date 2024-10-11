@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <!-- Colonne de champs principaux -->
        <div class="col-md-4">
            <h5>Champs disponibles</h5>
            <ul class="list-group" id="fields-list">
                <li class="list-group-item" data-field="code">Code</li>
                <li class="list-group-item" data-field="nom">Nom</li>
                <li class="list-group-item" data-field="contenu">Contenu</li>
                <li class="list-group-item" data-field="boite">Boite d’archives</li>
                <li class="list-group-item" data-field="etagere">Étagère</li>
                <li class="list-group-item" data-field="depot">Dépôt</li>
                <li class="list-group-item" data-field="tri">Tri</li>
                <li class="list-group-item" data-field="duree communicabilite">Durée de communicabilité</li>
                <li class="list-group-item" data-field="duree legale">Durée légale</li>
                <li class="list-group-item" data-field="auteur">Auteur</li>
                <li class="list-group-item" data-field="date creation">Date de création</li>
                <li class="list-group-item" data-field="statut">Statut</li>
                <li class="list-group-item" data-field="date debut">Date de début</li>
                <li class="list-group-item" data-field="date fin">Date de fin</li>
                <li class="list-group-item" data-field="date exacte">Date exacte</li>
                <li class="list-group-item" data-field="sort">Sort</li>
            </ul>
        </div>

        <!-- Colonne de champs de recherche dynamique -->
        <div class="col-md-8">
            <h5>Critères de recherche</h5>
            <form id="advanced-search-form" method="POST" action="{{ route('records.result')}}">
                @csrf
                <div id="search-criteria-container"></div>
                <button type="button" class="btn btn-primary mt-3" id="search-btn">Rechercher</button>
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
    document.addEventListener('DOMContentLoaded', function () {
        const fieldsList = document.getElementById('fields-list');
        const searchCriteriaContainer = document.getElementById('search-criteria-container');
        const searchCriteriaTemplate = document.getElementById('search-criteria-template');

        fieldsList.addEventListener('click', function (e) {
            if (e.target && e.target.nodeName === 'LI') {
                const fieldName = e.target.getAttribute('data-field');
                addSearchCriteria(fieldName);
            }
        });

        function addSearchCriteria(field) {
            const criteriaClone = searchCriteriaTemplate.content.cloneNode(true);
            const fieldNameInput = criteriaClone.querySelector('.field-name');
            const fieldLabel = criteriaClone.querySelector('.field-label');
            const operatorSelect = criteriaClone.querySelector('.field-operator');

            fieldNameInput.value = field;
            fieldLabel.textContent = field.charAt(0).toUpperCase() + field.slice(1);

            // Définir les options de tri en fonction du champ
            let operators = [];
            switch (field) {
                case 'code':
                case 'nom':
                case 'contenu':
                    operators = ['commence par', 'contient', 'ne contient pas'];
                    break;
                case 'date debut':
                case 'date fin':
                case 'date exacte':
                    operators = ['=', '>', '<'];
                    break;
                case 'duree_communicabilite':
                case 'duree_legale':
                    operators = ['=', '>', '<'];
                    break;
                case 'boite':
                case 'etagere':
                case 'depot':
                case 'auteur':
                case 'sort':
                    operators = ['='];
                    break;
                default:
                    operators = ['='];
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
