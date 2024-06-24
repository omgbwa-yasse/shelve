<div class="container" style="background-color: #f1f1f1;"> <!-- Couleur de fond marron -->
    <div class="row">
        <a class="nav-link active bg-dark text-white" data-toggle="collapse" href="#rechercheMenu" aria-expanded="true"
            aria-controls="rechercheMenu" style="padding: 10px;"><i class="bi bi-envelope"></i> Courrier </a>

        <div class="collapse show" id="rechercheMenu">

            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('mail-typology.index') }}"><i class="bi bi-tools"></i> Typologie de courrier </a>
                </li>

            </ul>
        </div>

        <a class="nav-link active bg-dark text-white" data-toggle="collapse" href="#rechercheMenu" aria-expanded="true"
            aria-controls="rechercheMenu" style="padding: 10px;"><i class="bi bi-file-text"></i> Repertoire </a>

        <div class="collapse show" id="rechercheMenu">

            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('buildings.index') }}"><i class="bi bi-tools"></i> xxxxxxxxxx </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('rooms.index') }}"><i class="bi bi-tools"></i> xxxxxxxxxx </a>
                </li>

            </ul>
        </div>


        <a class="nav-link active bg-dark text-white" data-toggle="collapse" href="#rechercheMenu" aria-expanded="true"
            aria-controls="rechercheMenu" style="padding: 10px;"><i class="bi bi-newspaper"></i> Versement </a>

        <div class="collapse show" id="rechercheMenu">

            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('buildings.index') }}"><i class="bi bi-tools"></i> xxxxxxxxxx</a>
                </li>

            </ul>
        </div>

        <a class="nav-link active bg-dark text-white" data-toggle="collapse" href="#rechercheMenu" aria-expanded="true"
        aria-controls="rechercheMenu" style="padding: 10px;"><i class="bi bi-building"></i> Dépôt </a>

    <div class="collapse show" id="rechercheMenu">

        <ul class="list-unstyled pl-3">
            <li class="nav-item">
                <a class="nav-link text-dark" href="{{ route('container-status.index') }}"><i class="bi bi-tools"></i> Status des contenants</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-dark" href="{{ route('container-property.index') }}"><i class="bi bi-tools"></i> Propriété de contenant </a>
            </li>

        </ul>
    </div>


    <a class="nav-link active bg-dark text-white" data-toggle="collapse" href="#rechercheMenu" aria-expanded="true"
        aria-controls="rechercheMenu" style="padding: 10px;"><i class="bi bi-tools"></i> Outils de gestion </a>

    <div class="collapse show" id="rechercheMenu">

        <ul class="list-unstyled pl-3">
            <li class="nav-item">
                <a class="nav-link text-dark" href="{{ route('sorts.index') }}"><i class="bi bi-tools"></i> Sorts finaux</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-dark" href="{{ route('container-property.index') }}"><i class="bi bi-tools"></i> xxxxxxxxxx </a>
            </li>

        </ul>
    </div>


    <div>
    </ul>
</div>
</div>
</div>
