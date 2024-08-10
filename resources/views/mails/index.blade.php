@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Liste des courriers</h1>
        <a href="{{ route('mails.create') }}" class="btn btn-primary mb-3">Create Mail</a>
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
                    <td>
                        @foreach($mail->container as $container)
                            {{ $container->code ?? '' }}
                            ({{ $container->name ?? 'Non conditionné' }})
                        @endforeach
                    </td>
                    <td>{{ $mail->type->name ?? '' }}</td>
                    <td>
                        <a href="{{ route('mails.show', $mail->id) }}" class="btn btn-info btn-sm">Détails</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>


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
