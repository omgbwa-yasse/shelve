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
                    <a class="nav-link text-dark" href="{{ route('dollies.action') }}?categ=record&sub=Date&id=14"><i class="bi bi-cart3"></i> Courrier</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href=""><i class="bi bi-cart3"></i> Archives</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href=""><i class="bi bi-cart3"></i> Communication</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href=""><i class="bi bi-cart3"></i> Versement</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href=""><i class="bi bi-cart3"></i> Bâtiments</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href=""><i class="bi bi-cart3"></i> Salle</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href=""><i class="bi bi-cart3"></i> Etagère</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href=""><i class="bi bi-cart3"></i> Boites d'archives</a>
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
