@extends('layouts.app')

@section('content')

<div class="table-responsive">
    <table class="table" id="mailArchivesTable">
        <thead>
            <tr>
                <th>
                    Conteneur
                    <input type="text" class="form-control form-control-sm filter-input" data-column="0" placeholder="Filtrer...">
                </th>
                <th>
                    Mail
                    <input type="text" class="form-control form-control-sm filter-input" data-column="1" placeholder="Filtrer...">
                </th>
                <th>
                    Type de document
                    <input type="text" class="form-control form-control-sm filter-input" data-column="2" placeholder="Filtrer...">
                </th>
                <th>
                    Archivé par
                    <input type="text" class="form-control form-control-sm filter-input" data-column="3" placeholder="Filtrer...">
                </th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($mailArchives as $mailArchive)
                <tr>
                    <td>{{ $mailArchive->container->code }}</td>
                    <td>{{ $mailArchive->mail->name }}</td>
                    <td>{{ $mailArchive->document_type }}</td>
                    <td>{{ $mailArchive->user->name }}</td>
                    <td>
                        <button type="button" 
                                class="btn btn-primary remove-mail-btn" 
                                data-archive-mail-id="{{ $mailArchive->id }}"
                                data-container-id="{{ $mailArchive->container->id }}" 
                                data-mail-id="{{ $mailArchive->mail->id }}"
                                data-remove-url="{{ route('mail-archive.remove-mails', $mailArchive->container->id) }}">
                            Remove
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>


<script>

    document.addEventListener('DOMContentLoaded', function() {
        // Sélectionner tous les boutons de suppression
        const removeButtons = document.querySelectorAll('.remove-mail-btn');

        // Ajouter un gestionnaire d'événement à chaque bouton
        removeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const containerId = this.dataset.containerId;
                const archiveMailId = this.dataset.archiveMailId;
                const removeUrl = this.dataset.removeUrl;

                if (confirm('Êtes-vous sûr de vouloir retirer ce mail de l\'archive ?')) {
                    // Utiliser fetch pour envoyer une requête AJAX
                    fetch(removeUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            archive_mail_id: archiveMailId,
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            
                            window.location.reload();
                        } else {
                            alert('Une erreur est survenue');
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Une erreur est survenue');
                    });
                }
            });
        });




         

        // Ajout du code pour les filtres
        const filterInputs = document.querySelectorAll('.filter-input');
        const table = document.getElementById('mailArchivesTable');
        const rows = table.querySelectorAll('tbody tr');

        filterInputs.forEach(input => {
            input.addEventListener('keyup', function() {
                const column = parseInt(this.dataset.column);
                const filterValue = this.value.toLowerCase();

                rows.forEach(row => {
                    const cell = row.querySelectorAll('td')[column];
                    if (cell) {
                        const text = cell.textContent.toLowerCase();
                        if (text.includes(filterValue)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    }
                });
            });
        });











    });

</script>
@endsection
