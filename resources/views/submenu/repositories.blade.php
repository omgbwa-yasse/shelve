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

        <!-- lifeCycle -->
        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#lifeCycleMenu" aria-expanded="false"
            aria-controls="lifeCycleMenu" style="padding: 10px;">
            <i class="bi bi-cart"></i> Circle de vie
        </a>
        <div class="collapse show" id="lifeCycleMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('records.tostore')}}"><i class="bi bi-folder-check"></i> A transferer</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('records.toretain')}}"><i class="bi bi-folder-check"></i> Dossiers actifs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('records.totransfer')}}"><i class="bi bi-arrow-right-square"></i> A verser </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('records.toeliminate')}}"><i class="bi bi-trash"></i> A éliminer</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('records.tokeep')}}"><i class="bi bi-archive"></i> A conserver</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('records.tosort')}}"><i class="bi bi-sort-down"></i> A trier</a>
                </li>

            </ul>
        </div>


        <!-- lifeCycle -->
        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#lifeCycleMenu" aria-expanded="false"
            aria-controls="lifeCycleMenu" style="padding: 10px;">
            <i class="bi bi-cart"></i> Import / Export (EAD, Excel, SEDA)
        </a>
        <div class="collapse show" id="lifeCycleMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('records.import.form') }}"><i class="bi bi-folder-check"></i> Import</a>
                </li>
                <li class="nav-item">

                    <a class="nav-link text-dark" href="{{ route('records.export.form') }}"><i class="bi bi-folder-check"></i> export</a>



            </ul>
        </div>


    </div>
</div>
