<div class="container">
    <div class="row">
        <!-- Plan de classement -->
        <a class="nav-link active bg-primary rounded-2 text-white d-flex align-items-center" data-toggle="collapse" href="#planClassementMenu"
           aria-expanded="true" style="padding: 10px;" aria-controls="planClassementMenu">
            <i class="bi bi-grid me-2"></i> {{ __('classification_plan') }}
        </a>
        <div class="collapse show" id="planClassementMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark d-flex align-items-center" href="{{ route('activities.index') }}"><i class="bi bi-list-check me-2"></i> {{ __('all_classes') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark d-flex align-items-center" href="{{ route('activities.create') }}"><i class="bi bi-plus-square me-2"></i> {{ __('add_class') }}</a>
                </li>
            </ul>
        </div>

        <!-- Référentiel de conservation -->
        <a class="nav-link active bg-primary rounded-2 text-white d-flex align-items-center" data-toggle="collapse" href="#referentielConservationMenu"
           aria-expanded="true" style="padding: 10px;" aria-controls="referentielConservationMenu">
            <i class="bi bi-archive me-2"></i> {{ __('retention_schedule') }}
        </a>
        <div class="collapse show" id="referentielConservationMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark d-flex align-items-center" href="{{ route('retentions.index') }}"><i class="bi bi-clock-history me-2"></i> {{ __('all_durations') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark d-flex align-items-center" href="{{ route('retentions.create') }}"><i class="bi bi-plus-square me-2"></i> {{ __('add_rule') }}</a>
                </li>
            </ul>
        </div>

        <!-- Communicabilité -->
        <a class="nav-link active bg-primary rounded-2 text-white d-flex align-items-center" data-toggle="collapse" href="#communicabiliteMenu"
           aria-expanded="false" style="padding: 10px;" aria-controls="communicabiliteMenu">
            <i class="bi bi-chat-square-text me-2"></i> {{ __('communicability') }}
        </a>
        <div class="collapse show" id="communicabiliteMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark d-flex align-items-center" href="{{ route('communicabilities.index')}}"><i class="bi bi-list-check me-2"></i> {{ __('all_classes') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark d-flex align-items-center" href="{{ route('communicabilities.create')}}"><i class="bi bi-plus-square me-2"></i> {{ __('add_class') }}</a>
                </li>
            </ul>
        </div>

        <!-- Organigramme -->
        <a class="nav-link active bg-primary rounded-2 text-white d-flex align-items-center" data-toggle="collapse" href="#organigrammeMenu"
           aria-expanded="false" style="padding: 10px;" aria-controls="organigrammeMenu">
            <i class="bi bi-diagram-3 me-2"></i> {{ __('organization_chart') }}
        </a>
        <div class="collapse show" id="organigrammeMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark d-flex align-items-center" href="{{ route('organisations.index')}}"><i class="bi bi-building me-2"></i> {{ __('all_units') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark d-flex align-items-center" href="{{ route('organisations.create')}}"><i class="bi bi-plus-square me-2"></i> {{ __('add_organization') }}</a>
                </li>
            </ul>
        </div>

        <!-- Thésaurus -->
        <a class="nav-link active bg-primary rounded-2 text-white d-flex align-items-center" data-toggle="collapse" href="#thesaurusMenu" aria-expanded="false"
           aria-controls="thesaurusMenu" style="padding: 10px;">
            <i class="bi bi-book-half me-2"></i> {{ __('thesaurus') }}
        </a>
        <div class="collapse show" id="thesaurusMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark d-flex align-items-center" href="{{ route('terms.index') }}"><i class="bi bi-tree me-2"></i> {{ __('view_branches') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark d-flex align-items-center" href="{{ route('terms.create') }}"><i class="bi bi-plus-square me-2"></i> {{ __('add_word') }}</a>
                </li>
            </ul>
        </div>

        <!-- Boite à outils -->
        <a class="nav-link active bg-primary rounded-2 text-white d-flex align-items-center" data-toggle="collapse" href="#outilsMenu" aria-expanded="false"
           aria-controls="outilsMenu" style="padding: 10px;">
            <i class="bi bi-tools me-2"></i> {{ __('toolbox') }}
        </a>
        <div class="collapse show" id="outilsMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark d-flex align-items-center" href="{{ route('barcode.create') }}"><i class="bi bi-upc-scan me-2"></i> {{ __('barcode') }}</a>
                </li>
            </ul>
        </div>
    </div>
</div>
