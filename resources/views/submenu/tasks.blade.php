<div class="container" style="background-color: #f1f1f1;">
    <div class="row">
        <!-- Recherche -->
        <a class="nav-link active bg-primary text-white" data-toggle="collapse" href="#rechercheMenu" aria-expanded="true"
           aria-controls="rechercheMenu" style="padding: 10px;"><i class="bi bi-search"></i> Recherche</a>

        <div class="collapse show" id="rechercheMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('tasks.index') }}"><i class="bi bi-inbox"></i> Tâches</a>
                </li>
                <!-- Ajoutez d'autres liens de recherche ici -->
            </ul>
        </div>

        <!-- Créer -->
        <a class="nav-link active bg-primary text-white" data-toggle="collapse" href="#enregistrementMenu" aria-expanded="true"
           aria-controls="enregistrementMenu" style="padding: 10px;">Créer</a>

        <div class="collapse show" id="enregistrementMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('tasks.create') }}"><i class="bi bi-inbox"></i> Tâche</a>
                </li>
                <!-- Ajoutez d'autres liens de création ici -->
            </ul>
        </div>

        <!-- Suivi des tâches -->
        <a class="nav-link active bg-primary text-white" data-toggle="collapse" href="#suiviMenu" aria-expanded="true"
           aria-controls="suiviMenu" style="padding: 10px;">Suivi des tâches</a>

        <div class="collapse show" id="suiviMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('tasks.index') }}"><i class="bi bi-inbox"></i> Tâches en cours</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('tasks.index') }}"><i class="bi bi-bookmark-check"></i> Tâches terminées</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('tasks.my_tasks') }}"><i class="bi bi-bookmark-check"></i> Mes Tâches </a>
                </li>
            </ul>
        </div>
    </div>
</div>
