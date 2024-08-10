@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Records</h1>
        <a href="{{ route('records.create') }}" class="btn btn-primary mb-3">Create Record</a>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Code</th>
                <th>Name</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($records as $record)
                <tr>
                    <td>{{ $record->id }}</td>
                    <td>{{ $record->code }}</td>
                    <td>{{ $record->name }}</td>
                    <td>
                        <a href="{{ route('records.show', $record) }}" class="btn btn-sm btn-info">Voir la fiche</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <script>
        document.getElementById('term_id').addEventListener('change', function () {
            let selectedOptions = Array.from(this.selectedOptions);
            selectedOptions.forEach(option => {
                addTerm(option.text, option.value);
            });
            this.selectedOptions = [];
        });

        document.getElementById('term_search').addEventListener('input', function () {
            let searchQuery = this.value.toLowerCase();
            let termOptions = document.getElementById('term_id').options;

            for (let i = 0; i < termOptions.length; i++) {
                let option = termOptions[i];
                option.style.display = option.text.toLowerCase().includes(searchQuery) ? 'block' : 'none';
            }
        });

        function addTerm(termName, termId) {
            let selectedTerms = document.getElementById('selected-terms');
            let termItem = document.createElement('div');
            termItem.classList.add('list-group-item', 'd-flex', 'justify-content-between', 'align-items-center');

            let termNameSpan = document.createElement('span');
            termNameSpan.textContent = termName;

            let removeButton = document.createElement('button');
            removeButton.classList.add('btn', 'btn-sm', 'btn-danger');
            removeButton.textContent = 'Supprimer';
            removeButton.onclick = function () {
                termItem.remove();
                updateTermIds();
            };

            termItem.appendChild(termNameSpan);
            termItem.appendChild(removeButton);
            selectedTerms.appendChild(termItem);

            updateTermIds();
        }

        function updateTermIds() {
            let termIds = Array.from(document.getElementById('selected-terms').children).map(item => item.getAttribute('data-id'));
            document.getElementById('term-ids').value = termIds.join(',');
        }
    </script>
@endsection
