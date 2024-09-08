<div class="container" style="background-color: #f1f1f1;"> <!-- Couleur de fond marron -->
    <div class="row">


        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#rechercheMenu" aria-expanded="true"
            aria-controls="rechercheMenu" style="padding: 10px;"><i class="bi bi-search"></i>Recherche</a>

        <div class="collapse show" id="rechercheMenu">

            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('slips.index') }}"><i class="bi bi-building"></i> Tous mes bordereauX</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('slips-select-date') }}"><i class="bi bi-list"></i> Dates</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('slips-select-organisation') }}?categ=organisation"><i class="bi bi-list"></i> Organisations</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('slips-sort') }}?categ=draft"><i class="bi bi-list"></i> En cours</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('slips-sort') }}?categ=approved"><i class="bi bi-list"></i> Approvés</a>
                </li>

            </ul>
        </div>


        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#rechercheMenu" aria-expanded="true"
            aria-controls="rechercheMenu" style="padding: 10px;"><i class="bi bi-search"></i> Suivi de transfert </a>

        <div class="collapse show" id="rechercheMenu">

            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('slips.sort') }}?categ=project"><i class="bi bi-folder"></i> Projets</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('slips.sort') }}?categ=received"><i class="bi bi-envelope-check"></i> Reçus</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('slips.sort') }}?categ=approved"><i class="bi bi-check-circle"></i> Approuvés</a>
                </li>
            </ul>
        </div>



        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#enregistrementMenu"
            aria-expanded="true" aria-controls="enregistrementMenu" style="padding: 10px;">Créer</a>
            <div class="collapse show" id="enregistrementMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('slips.create') }}"><i class="bi bi-building"></i>Bordereau</a>
                </li>
            </ul>
        </div>


        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#lifeCycleMenu" aria-expanded="false"
            aria-controls="lifeCycleMenu" style="padding: 10px;">
            <i class="bi bi-cart"></i> Import / Export (EAD, Excel, SEDA)
        </a>
        <div class="collapse show" id="lifeCycleMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href=""><i class="bi bi-folder-check"></i> Import</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href=""><i class="bi bi-folder-check"></i> Export</a>
                </li>
            </ul>
        </div>

        <div>
        </ul>
        </div>
    </div>
</div>
