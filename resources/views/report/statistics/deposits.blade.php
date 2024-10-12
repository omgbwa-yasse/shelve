<div class="container" style="background-color: #f1f1f1;"> <!-- Couleur de fond marron -->
    <div class="row">
        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#rechercheMenu" aria-expanded="true"
            aria-controls="rechercheMenu" style="padding: 10px;"><i class="bi bi-search"></i>Recherche</a>

        <div class="collapse show" id="rechercheMenu">

            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('buildings.index') }}"><i class="bi bi-building"></i> Bâtiment</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('rooms.index') }}"><i class="bi bi-house"></i> Salle</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('shelves.index') }}"><i class="bi bi-bookshelf"></i> Etagères</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('containers.index') }}"><i class="bi bi-box"></i> Contenant d'archives</a>
                </li>
            </ul>
        </div>

        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#enregistrementMenu"
            aria-expanded="true" aria-controls="enregistrementMenu" style="padding: 10px;">Créer</a>

            <div class="collapse show" id="enregistrementMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('buildings.create') }}"><i class="bi bi-building"></i> Batiment</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('rooms.create') }}"><i class="bi bi-house"></i> Salle</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('shelves.create') }}"><i class="bi bi-bookshelf"></i> étagère</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('containers.create') }}"><i class="bi bi-archive"></i> Contenant d'archives</a>
                </li>
            </ul>
        </div>

            <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#chariotMenu"
            aria-expanded="true" aria-controls="chariotMenu" style="padding: 10px;">Mes chariots</a>

            <div class="collapse show" id="chariotMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('buildings.create') }}"><i class="bi bi-building"></i> Bâtiments</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('rooms.create') }}"><i class="bi bi-house"></i> Salle</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('shelves.create') }}"><i class="bi bi-bookshelf"></i> étagères</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('containers.create') }}"><i class="bi bi-archive"></i> contenant d'archives</a>
                </li>
                </ul>
            </div>
            <div>
            </ul>
        </div>
    </div>
</div>
