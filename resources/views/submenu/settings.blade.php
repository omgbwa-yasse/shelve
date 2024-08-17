<div class="container" style="background-color: #f1f1f1;"> <!-- Couleur de fond marron -->
    <div class="row">
        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#rechercheMenu" aria-expanded="true"
            aria-controls="rechercheMenu" style="padding: 10px;"><i class="bi bi-people"></i> Mon compte </a>

        <div class="collapse show" id="rechercheMenu">

            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('users.show', auth()->user()->id) }}"><i class="bi bi-gear"></i> Mon compte </a>
                </li>

            </ul>
        </div>

        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#rechercheMenu" aria-expanded="true"
        aria-controls="rechercheMenu" style="padding: 10px;"><i class="bi bi-people"></i> Comptes utilisateurs </a>

    <div class="collapse show" id="rechercheMenu">

        <ul class="list-unstyled pl-3">

            <li class="nav-item">
                <a class="nav-link text-dark" href="{{ route('user-roles.index') }}"><i class="bi bi-gear"></i> Utilisateurs et habilités </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-dark" href="{{ route('users.index') }}"><i class="bi bi-gear"></i> Utilisateurs </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-dark" href="{{ route('user-organisations.index') }}"><i class="bi bi-gear"></i> Postes affectés </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-dark" href="{{ route('roles.index') }}"><i class="bi bi-gear"></i> Rôles </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-dark" href="{{ route('role_permissions.index') }}"><i class="bi bi-gear"></i> Affecter des permissions </a>
            </li>


        </ul>
    </div>



        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#rechercheMenu" aria-expanded="true"
            aria-controls="rechercheMenu" style="padding: 10px;"><i class="bi bi-envelope"></i> Courrier </a>

        <div class="collapse show" id="rechercheMenu">

            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('mail-typology.index') }}"><i class="bi bi-gear"></i> Typologies </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('mail-action.index') }}"><i class="bi bi-gear"></i> Actions </a>
                </li>

            </ul>
        </div>

        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#rechercheMenu" aria-expanded="true"
            aria-controls="rechercheMenu" style="padding: 10px;"><i class="bi bi-file-text"></i> Repertoire </a>

        <div class="collapse show" id="rechercheMenu">

            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('record-supports.index') }}"><i class="bi bi-gear"></i> Supports  </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('record-statuses.index') }}"><i class="bi bi-gear"></i> Status  </a>
                </li>

            </ul>
        </div>


        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#rechercheMenu" aria-expanded="true"
            aria-controls="rechercheMenu" style="padding: 10px;"><i class="bi bi-newspaper"></i> Versement </a>

        <div class="collapse show" id="rechercheMenu">

            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('transferring-status.index') }}"><i class="bi bi-gear"></i> Status</a>
                </li>

            </ul>
        </div>

        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#communicationMenu" aria-expanded="true"
            aria-controls="communicationMenu" style="padding: 10px;"><i class="bi bi-newspaper"></i> Communication </a>

        <div class="collapse show" id="communicationMenu">

            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('communication-status.index') }}"><i class="bi bi-gear"></i> Status de communication </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('reservation-status.index') }}"><i class="bi bi-gear"></i> Status de reservation</a>
                </li>

            </ul>
        </div>

        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#rechercheMenu" aria-expanded="true"
        aria-controls="rechercheMenu" style="padding: 10px;"><i class="bi bi-building"></i> Dépôt </a>

    <div class="collapse show" id="rechercheMenu">

        <ul class="list-unstyled pl-3">
            <li class="nav-item">
                <a class="nav-link text-dark" href="{{ route('container-status.index') }}"><i class="bi bi-gear"></i> Status des contenants</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-dark" href="{{ route('container-property.index') }}"><i class="bi bi-gear"></i> Propriété de contenant </a>
            </li>

        </ul>
    </div>


    <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#rechercheMenu" aria-expanded="true"
        aria-controls="rechercheMenu" style="padding: 10px;"><i class="bi bi-gear"></i> Outils de gestion </a>

    <div class="collapse show" id="rechercheMenu">

        <ul class="list-unstyled pl-3">
            <li class="nav-item">
                <a class="nav-link text-dark" href="{{ route('sorts.index') }}"><i class="bi bi-gear"></i> Retention : Sorts finaux</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-dark" href="{{ route('languages.index') }}"><i class="bi bi-gear"></i> Thesaurus : langues </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-dark" href="{{ route('term-categories.index') }}"><i class="bi bi-gear"></i> Thésaurus : categories </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-dark" href="{{ route('term-equivalent-types.index') }}"><i class="bi bi-gear"></i> Thésaurus : équivalents </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-dark" href="{{ route('term-types.index') }}"><i class="bi bi-gear"></i> Thésaurus : types </a>
            </li>
        </ul>
    </div>


    <div>
    </ul>
</div>
</div>
</div>
