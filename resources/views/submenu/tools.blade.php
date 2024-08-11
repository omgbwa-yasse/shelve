<div class="container-fluid p-0">
    <div class="sidebar bg-light border-end">
        <ul class="nav flex-column">
            <!-- Plan de classement -->
            <li class="nav-item">
                <a class="nav-link text-dark d-flex align-items-center" data-bs-toggle="collapse" href="#planClassementMenu" role="button" aria-expanded="true" aria-controls="planClassementMenu">
                    <i class="bi bi-grid me-2"></i>
                    <span>Plan de classement</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse show" id="planClassementMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link text-muted" href="{{ route('activities.index') }}">
                                <i class="bi bi-list-check me-2"></i> Toutes les classes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-muted" href="{{ route('activities.create') }}">
                                <i class="bi bi-plus-square me-2"></i> Ajouter une classe
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Référentiel de conservation -->
            <li class="nav-item">
                <a class="nav-link text-dark d-flex align-items-center" data-bs-toggle="collapse" href="#referentielConservationMenu" role="button" aria-expanded="true" aria-controls="referentielConservationMenu">
                    <i class="bi bi-archive me-2"></i>
                    <span>Référentiel de conservation</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse show" id="referentielConservationMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link text-muted" href="{{ route('retentions.index') }}">
                                <i class="bi bi-clock-history me-2"></i> Tous les durées
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-muted" href="{{ route('retentions.create') }}">
                                <i class="bi bi-plus-square me-2"></i> Ajouter un règle
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Communicabilité -->
            <li class="nav-item">
                <a class="nav-link text-dark d-flex align-items-center" data-bs-toggle="collapse" href="#communicabiliteMenu" role="button" aria-expanded="true" aria-controls="communicabiliteMenu">
                    <i class="bi bi-chat-square-text me-2"></i>
                    <span>Communicabilité</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse show" id="communicabiliteMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link text-muted" href="{{ route('communicabilities.index')}}">
                                <i class="bi bi-list-check me-2"></i> Toutes les classes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-muted" href="{{ route('communicabilities.create')}}">
                                <i class="bi bi-plus-square me-2"></i> Ajouter une classe
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Organigramme -->
            <li class="nav-item">
                <a class="nav-link text-dark d-flex align-items-center" data-bs-toggle="collapse" href="#organigrammeMenu" role="button" aria-expanded="true" aria-controls="organigrammeMenu">
                    <i class="bi bi-diagram-3 me-2"></i>
                    <span>Organigramme</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse show" id="organigrammeMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link text-muted" href="{{ route('organisations.index')}}">
                                <i class="bi bi-building me-2"></i> Toutes les unités
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-muted" href="{{ route('organisations.create')}}">
                                <i class="bi bi-plus-square me-2"></i> Ajouter une organisation
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Thésaurus -->
            <li class="nav-item">
                <a class="nav-link text-dark d-flex align-items-center" data-bs-toggle="collapse" href="#thesaurusMenu" role="button" aria-expanded="true" aria-controls="thesaurusMenu">
                    <i class="bi bi-book-half me-2"></i>
                    <span>Thésaurus</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse show" id="thesaurusMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link text-muted" href="{{ route('terms.index') }}">
                                <i class="bi bi-tree me-2"></i> Voir les branches
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-muted" href="{{ route('terms.create') }}">
                                <i class="bi bi-plus-square me-2"></i> Ajouter un mot
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
    </div>
</div>
