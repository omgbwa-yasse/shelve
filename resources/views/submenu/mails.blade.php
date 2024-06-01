<div class="container" style="background-color: #f1f1f1;"> <!-- Couleur de fond marron -->
    <div class="row">
        <a class="nav-link active bg-dark text-white" data-toggle="collapse" href="#rechercheMenu" aria-expanded="true"
            aria-controls="rechercheMenu" style="padding: 10px;"><i class="bi bi-search"></i> Recherche</a>

        <div class="collapse show" id="rechercheMenu">

            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#"><i class="bi bi-inbox"></i> Reçus</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#"><i class="bi bi-envelope"></i> Envoyés</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#"><i class="bi bi-briefcase"></i> Affaires</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#"><i class="bi bi-tags"></i> Typologies</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#"><i class="bi bi-people"></i> Producteurs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#"><i class="bi bi-calendar"></i> Dates</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#"><i class="bi bi-arrow-up-right-square"></i>
                        Avancée</a>
                </li>
            </ul>
        </div>
        <a class="nav-link active bg-dark text-white" data-toggle="collapse" href="#enregistrementMenu"
            aria-expanded="true" aria-controls="enregistrementMenu" style="padding: 10px;">Enregistrement</a>
        <div class="collapse show" id="enregistrementMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('mails.received') }}"><i class="bi bi-inbox"></i> Courrier reçu</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('mails.send') }}"><i class="bi bi-envelope"></i> Courrier envoyé</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#"><i class="bi bi-briefcase"></i> Affaire</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#"><i class="bi bi-bookmark-check"></i> Parapheur</a>
                </li>
            </ul>
        </div>
        <a class="nav-link active bg-dark text-white" data-toggle="collapse" href="#etatMenu" aria-expanded="true"
            aria-controls="etatMenu" style="padding: 10px;">Etat</a>
        <div class="collapse show" id="etatMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#"><i class="bi bi-cash-stack"></i>Transactions</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#"><i class="bi bi-building"></i> Locaux</a>
                </li>
            </ul>
        </div>
    </div>
</div>
