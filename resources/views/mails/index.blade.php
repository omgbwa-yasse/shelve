@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Liste des courriers</h1>
        <a href="{{ route('mails.create') }}" class="btn btn-primary mb-3">Create Mail</a>

        <form action="{{ route('mails.search') }}" method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-6">
                    <input type="text" name="code" class="form-control" placeholder="Recherche par code">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">Rechercher</button>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-secondary" data-toggle="collapse" data-target="#advancedSearch">Recherche avancée</button>
                </div>
            </div>

            <div class="collapse mt-3" id="advancedSearch">
                <div class="card card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <input type="text" name="name" class="form-control" placeholder="Objet">
                        </div>
                        <div class="col-md-6 mb-3">
                            <input type="text" name="author" class="form-control" placeholder="Auteur">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <input type="date" name="date" class="form-control" placeholder="Date">
                        </div>
                        <div class="col-md-6 mb-3">
                            <select name="mail_priority_id" class="form-control">
                                <option value="">Sélectionner une priorité</option>
                                @foreach($priorities as $priority)
                                    <option value="{{ $priority->id }}">{{ $priority->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <select name="mail_type_id" class="form-control">
                                <option value="">Sélectionner un type</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <select name="mail_typology_id" class="form-control">
                                <option value="">Sélectionner une typologie</option>
                                @foreach($typologies as $typology)
                                    <option value="{{ $typology->id }}">{{ $typology->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="author_search">Rechercher des auteurs</label>
                            <input type="text" id="author_search" class="form-control" placeholder="Taper pour rechercher...">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <select id="author_id" class="form-select" multiple>
                                @foreach ($authors as $author)
                                    <option value="{{ $author->id }}">{{ $author->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div id="selected-authors" class="mt-3"></div>
                    <input type="hidden" name="author_ids" id="author-ids">
                </div>
            </div>
        </form>

        <table class="table">
            <thead>
            <tr>
                <th>Code</th>
                <th>Object, Auteur</th>
                <th>Date</th>
                <th>Producteur</th>
                <th>Localisation</th>
                <th>Type</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($mails as $mail)
                <tr>
                    <td>{{ $mail->code }}</td>
                    <td>
                        {{ $mail->name }},
                        @foreach($mail->authors as $author)
                            {{ $author->name }}
                        @endforeach
                    </td>
                    <td>{{ $mail->date }}</td>
                    <td>{{ $mail->creator->name ?? '' }}</td>
                    <td>{{ $mail->container->name ?? '' }}</td>
                    <td>{{ $mail->type->name ?? '' }}</td>
                    <td>
                        <a href="{{ route('mails.show', $mail->id) }}" class="btn btn-info btn-sm">Détails</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{ $mails->links() }}
    </div>

    <script>
        document.getElementById('author_id').addEventListener('change', function () {
            let selectedOptions = Array.from(this.selectedOptions);
            selectedOptions.forEach(option => {
                addAuthor(option.text, option.value);
            });
            this.selectedOptions = [];
        });

        document.getElementById('author_search').addEventListener('input', function () {
            let searchQuery = this.value.toLowerCase();
            let authorOptions = document.getElementById('author_id').options;

            for (let i = 0; i < authorOptions.length; i++) {
                let option = authorOptions[i];
                option.style.display = option.text.toLowerCase().includes(searchQuery) ? 'block' : 'none';
            }
        });

        function addAuthor(authorName, authorId) {
            let selectedAuthors = document.getElementById('selected-authors');
            let authorItem = document.createElement('div');
            authorItem.classList.add('list-group-item', 'd-flex', 'justify-content-between', 'align-items-center');

            let authorNameSpan = document.createElement('span');
            authorNameSpan.textContent = authorName;

            let removeButton = document.createElement('button');
            removeButton.classList.add('btn', 'btn-sm', 'btn-danger');
            removeButton.textContent = 'Supprimer';
            removeButton.onclick = function () {
                authorItem.remove();
                updateAuthorIds();
            };

            authorItem.appendChild(authorNameSpan);
            authorItem.appendChild(removeButton);
            selectedAuthors.appendChild(authorItem);

            updateAuthorIds();
        }

        function updateAuthorIds() {
            let authorIds = Array.from(document.getElementById('selected-authors').children).map(item => item.getAttribute('data-id'));
            document.getElementById('author-ids').value = authorIds.join(',');
        }
    </script>
@endsection
