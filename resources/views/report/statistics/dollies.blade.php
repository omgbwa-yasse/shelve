<div class="container" style="background-color: #f1f1f1;"> <!-- Couleur de fond marron -->
    <div class="row">
        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#rechercheMenu" aria-expanded="true"
            aria-controls="rechercheMenu" style="padding: 10px;"><i class="bi bi-search"></i>Recherche</a>

        <div class="collapse show" id="rechercheMenu">

            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('dolly.index') }}"><i class="bi bi-cart3"></i> Tous les chariots</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('dollies-sort')}}?categ=mail"><i class="bi bi-cart3"></i> Courrier</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('dollies-sort')}}?categ=record"><i class="bi bi-cart3"></i> Archives</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('dollies-sort')}}?categ=communication"><i class="bi bi-cart3"></i> Communication </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('dollies-sort')}}?categ=room"><i class="bi bi-cart3"></i> Salle </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('dollies-sort')}}?categ=shelf"><i class="bi bi-cart3"></i> Etagère </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('dollies-sort')}}?categ=container"><i class="bi bi-cart3"></i> Boites d'archives </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('dollies-sort')}}?categ=slip_record"><i class="bi bi-cart3"></i> Archives (versement) </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('dollies-sort')}}?categ=slip"><i class="bi bi-cart3"></i> Versement </a>
                </li>
            </ul>
        </div>

        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#enregistrementMenu"
            aria-expanded="true" aria-controls="enregistrementMenu" style="padding: 10px;">Créer</a>

            <div class="collapse show" id="enregistrementMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('dolly.create') }}"><i class="bi bi-cart3"></i>Chariot</a>
                </li>
            </ul>
        </div>


            <div>
            </ul>
        </div>
    </div>
</div>
