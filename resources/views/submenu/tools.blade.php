<div class="container">
    <div class="row">
        <!-- Plan de classement -->
        <a class="nav-link active bg-dark text-white" data-toggle="collapse" href="#planClassementMenu"
            aria-expanded="true" aria-controls="planClassementMenu">
            <i class="bi bi-grid"></i> Plan de classement
        </a>
        <div class="collapse show" id="planClassementMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('activities.index') }}"><i class="bi bi-list-check"></i> Toutes les classes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('activities.create') }}"><i class="bi bi-plus-square"></i> Ajouter une
                        classe</a>
                </li>
            </ul>
        </div>

        <!-- Référentiel de conservation -->
        <a class="nav-link active bg-dark text-white" data-toggle="collapse" href="#referentielConservationMenu"
            aria-expanded="true" aria-controls="referentielConservationMenu">
            <i class="bi bi-archive"></i> Référentiel de conservation
        </a>
        <div class="collapse show" id="referentielConservationMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('retentions.index') }}"><i class="bi bi-clock-history"></i> Tous les durées</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('retentions.create') }}"><i class="bi bi-plus-square"></i> Ajouter un règle</a>
                </li>
            </ul>
        </div>

        <!-- Communicabilité -->
        <a class="nav-link active bg-dark text-white" data-toggle="collapse" href="#communicabiliteMenu"
            aria-expanded="false" aria-controls="communicabiliteMenu">
            <i class="bi bi-chat-square-text"></i> Communicabilité
        </a>
        <div class="collapse show" id="communicabiliteMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('communicabilities.index')}}"><i class="bi bi-list-check"></i> Toutes les classes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('communicabilities.create')}}"><i class="bi bi-plus-square"></i> Ajouter une
                        classe</a>
                </li>
            </ul>
        </div>

        <!-- Organigramme -->
        <a class="nav-link active bg-dark text-white" data-toggle="collapse" href="#organigrammeMenu"
            aria-expanded="false" aria-controls="organigrammeMenu">
            <i class="bi bi-diagram-3"></i> Organigramme
        </a>
        <div class="collapse show" id="organigrammeMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('organisations.index')}}"><i class="bi bi-building"></i> Toutes les unités</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('organisations.create')}}"><i class="bi bi-plus-square"></i> Ajouter une
                        organisation</a>
                </li>
            </ul>
        </div>

        <!-- Thésaurus -->
        <a class="nav-link active bg-dark text-white" data-toggle="collapse" href="#thesaurusMenu" aria-expanded="false"
            aria-controls="thesaurusMenu">
            <i class="bi bi-book-half"></i> Thésaurus
        </a>
        <div class="collapse show" id="thesaurusMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('terms.index') }}"><i class="bi bi-tree"></i> voir les branches</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('terms.create') }}"><i class="bi bi-plus-square"></i> Ajouter un mot</a>
                </li>
            </ul>
        </div>
    </div>
</div>
