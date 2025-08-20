@extends('layouts.app')

@section('content')

<form action="{{ route('mails.advanced') }}" method="POST" class="mb-4">
    @csrf

  <div class="row">
    <div class="col-md-6">
      <input type="text" name="code" class="form-control" placeholder="Recherche par code">
    </div>
    <div class="col-md-3">
      <button type="submit" class="btn btn-primary">Rechercher</button>
    </div>
    <div class="col-md-3">
      <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#advancedSearch">Recherche
        avancée</button>
    </div>
  </div>

  <div class="collapse mt-3" id="advancedSearch">
    <div class="card card-body">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="name">Objet :</label>
          <input type="text" name="name" class="form-control" id="name" placeholder="Objet">
        </div>
        <div class="col-md-6 mb-3">
          <label for="author">Auteur :</label>
          <div class="input-group">
            <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#authorModal">
              <i class="bi bi-search"></i>
            </button>
            <input type="text" name="author" class="form-control" id="author" placeholder="Auteur" readonly>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="date">Date :</label>
          <input type="date" name="date" class="form-control" id="date">
        </div>
        <div class="col-md-6 mb-3">
          <label for="mail_priority_id">Priorité :</label>
          <select name="mail_priority_id" class="form-select" id="mail_priority_id">
            <option value="">Sélectionner une priorité</option>
            @foreach($data['priorities'] ?? [] as $priority)
              <option value="{{ $priority->id }}">{{ $priority->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="mail_type_id">Type :</label>
          <select name="mail_type_id" class="form-select" id="mail_type_id">
            <option value="">Sélectionner un type</option>
            @foreach($data['types'] ?? [] as $type)
              <option value="{{ $type->id }}">{{ $type->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-6 mb-3">
          <label for="mail_typology_id">Typologie :</label>
          <select name="mail_typology_id" class="form-select" id="mail_typology_id">
            <option value="">Sélectionner une typologie</option>
            @foreach($data['typologies'] ?? [] as $typology)
              <option value="{{ $typology->id }}">{{ $typology->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="document_type_id">Type de document :</label>
          <select name="document_type_id" class="form-select" id="document_type_id">
            <option value="">Sélectionner un type de document</option>
            @foreach($data['documentTypes'] ?? [] as $documentType)
              <option value="{{ $documentType->id }}">{{ $documentType->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-6 mb-3">
          <label for="container_id">Conteneur :</label>
          <select name="container_id" class="form-select" id="container_id">
            <option value="">Sélectionner un conteneur</option>
            @foreach($data['containers'] ?? [] as $container)
              <option value="{{ $container->id }}">{{ $container->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="attachment_content">Contenu des pièces jointes :</label>
          <input type="text" name="attachment_content" class="form-control" id="attachment_content" placeholder="Mot/phrase contenu dans les pièces jointes">
        </div>
      </div>
    </div>
  </div>

  <input type="hidden" name="author_ids" id="author-ids">

</form>

<div class="modal fade" id="authorModal" tabindex="-1" aria-labelledby="authorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="authorModalLabel">Sélectionner des auteurs</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="selectedAuthors" class="mb-3"></div>
        <div class="mb-3">
          <input type="text" class="form-control" id="authorSearchInput" placeholder="Rechercher un auteur">
        </div>
        <div class="list-group" id="authorList">
          @foreach ($data['authors'] ?? [] as $author)
            <label class="list-group-item">
              <input class="form-check-input me-1 author-checkbox" type="checkbox" value="{{ $author->id }}">
              {{ $author->name }}
            </label>
          @endforeach
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
        <button type="button" class="btn btn-primary" id="selectAuthorsBtn">Sélectionner</button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const authorSearchInput = document.getElementById('authorSearchInput');
    const authorList = document.getElementById('authorList');
    const selectedAuthorsDiv = document.getElementById('selectedAuthors');
    const authorCheckboxes = document.querySelectorAll('.author-checkbox');
    const selectAuthorsBtn = document.getElementById('selectAuthorsBtn');
    const authorIdsInput = document.getElementById('author-ids');
    const authorInput = document.getElementById('author');

    if (authorSearchInput) {
      authorSearchInput.addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const authors = authorList.querySelectorAll('label');

        authors.forEach(author => {
          const authorName = author.textContent.trim().toLowerCase();
          author.style.display = authorName.includes(filter) ? 'block' : 'none';
        });
      });
    }

    function updateSelectedAuthors() {
      if (!selectedAuthorsDiv) return;

      selectedAuthorsDiv.innerHTML = '';
      const selectedAuthorNames = [];

      authorCheckboxes.forEach(checkbox => {
        if (checkbox.checked) {
          const authorName = checkbox.parentNode.textContent.trim();
          const span = document.createElement('span');
          span.classList.add('badge', 'bg-primary', 'me-2');
          span.textContent = authorName;
          selectedAuthorsDiv.appendChild(span);
          selectedAuthorNames.push(authorName);
        }
      });

      if (authorInput) {
        authorInput.value = selectedAuthorNames.join('; ');
      }
    }

    authorCheckboxes.forEach(checkbox => {
      checkbox.addEventListener('change', updateSelectedAuthors);
    });

    if (selectAuthorsBtn) {
      selectAuthorsBtn.addEventListener('click', function() {
        const selectedAuthors = [];
        authorList.querySelectorAll('input:checked').forEach(checkbox => {
          selectedAuthors.push(checkbox.value);
        });

        if (authorIdsInput) {
          authorIdsInput.value = selectedAuthors.join(',');
        }

        const modal = document.getElementById('authorModal');
        if (modal && bootstrap) {
          const modalInstance = bootstrap.Modal.getInstance(modal);
          if (modalInstance) {
            modalInstance.hide();
          }
        }
      });
    }
  });
</script>
@endpush
