<div class="container">
    <div class="row">
        <!-- Recherche -->
        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#rechercheMenu" aria-expanded="false"
        style="padding: 10px;" aria-controls="rechercheMenu">
            <i class="bi bi-search"></i> Recherche
        </a>
        <div class="collapse show" id="rechercheMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('records.index') }}"><i class="bi bi-list-check"></i> Tous les
                        enregistrements</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('record-author.index') }}"><i class="bi bi-person"></i> Detenteurs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('record-select-date')}}"><i class="bi bi-calendar"></i> Dates</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('record-select-word')}}"><i class="bi bi-key"></i> Mots-clés</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('record-select-activity')}}"><i class="bi bi-briefcase"></i> Activités</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('record-select-building')}}"><i class="bi bi-building"></i> Locaux</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('record-select-last')}}"><i class="bi bi-clock-history"></i> Derniers
                        enregistrements</a>
                </li>
            </ul>
        </div>

        <!-- Enregistrement -->
        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#enregistrementMenu"
            aria-expanded="false" style="padding: 10px;" aria-controls="enregistrementMenu">
            <i class="bi bi-journal-plus"></i> Enregistrement
        </a>
        <div class="collapse show" id="enregistrementMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('records.create') }}"><i class="bi bi-plus-square"></i> Nouveau</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('record-author.create') }}"><i class="bi bi-plus-square"></i> Producteur</a>
                </li>
            </ul>
        </div>

        <!-- Chariot -->
        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#chariotMenu" aria-expanded="false"
            aria-controls="chariotMenu" style="padding: 10px;">
            <i class="bi bi-cart"></i> Chariot
        </a>
        <div class="collapse show" id="chariotMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#"><i class="bi bi-gear"></i> Gestion</a>
                </li>
            </ul>
        </div>
    </div>
</div>
