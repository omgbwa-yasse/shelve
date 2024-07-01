<div class="container">
    <div class="row">
        <!-- Recherche -->
        <a class="nav-link active bg-dark text-white" data-toggle="collapse" href="#rechercheMenu" aria-expanded="true"
            aria-controls="rechercheMenu" style="padding: 10px;">
            <i class="bi bi-search"></i> Recherche
        </a>
        <div class="collapse show" id="rechercheMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('transactions.index')}}"><i class="bi bi-inbox"></i> Liste de sortie</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#"><i class="bi bi-inbox"></i> Par Demandeur</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#"><i class="bi bi-envelope"></i> Par Organisation</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#"><i class="bi bi-envelope"></i> Par Date</a>
                </li>
            </ul>
        </div>

        <!-- Communication -->
        <a class="nav-link active bg-dark text-white" data-toggle="collapse" href="#CommunicationMenu"
            aria-expanded="true" aria-controls="CommunicationMenu" style="padding: 10px;">
            <i class="bi bi-journal-plus"></i> Ajouter
        </a>
        <div class="collapse show" id="CommunicationMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('transactions.create')}}"><i class="bi bi-inbox"></i> Transaction</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#"><i class="bi bi-envelope"></i> Réservation</a>
                </li>

            </ul>
        </div>

        <!-- Etat -->
        <a class="nav-link active bg-dark text-white" data-toggle="collapse" href="#etatMenu" aria-expanded="true"
            aria-controls="etatMenu">
            <i class="bi bi-info-circle" style="padding: 10px;"></i> Panier
        </a>
        <div class="collapse show" id="etatMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#"><i class="bi bi-cash-stack"></i> Gestion</a>
                </li>
            </ul>
        </div>
    </div>
</div>
