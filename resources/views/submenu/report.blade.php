<div class="container" style="background-color: #f1f1f1;">
    <div class="row">
        <!-- Tâches -->
        <a class="nav-link active bg-primary text-white" data-toggle="collapse" href="#tachesMenu" aria-expanded="true"
            aria-controls="tachesMenu" style="padding: 10px;"><i class="bi bi-list-task"></i> Tâches</a>

        <div class="collapse show" id="tachesMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('report.my-tasks') }}"><i
                            class="bi bi-person-task"></i> Mes tâches</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('report.all-tasks') }}"><i class="bi bi-tasks"></i>
                        Toutes les tâches</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('report.create-task') }}"><i
                            class="bi bi-plus-circle"></i> Créer une tâche</a>
                </li>
            </ul>
        </div>

        <!-- Statistiques -->
        <a class="nav-link active bg-primary text-white" data-toggle="collapse" href="#statistiquesMenu"
            aria-expanded="true" aria-controls="statistiquesMenu" style="padding: 10px;"><i class="bi bi-bar-chart"></i>
            Statistiques</a>

        <div class="collapse show" id="statistiquesMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('report.statistics.mails') }}"><i
                            class="bi bi-graph-up"></i> Courrier</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('report.statistics.repositories') }}"><i
                            class="bi bi-graph-up"></i> Répertoire</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('report.statistics.communications') }}"><i
                            class="bi bi-graph-up"></i> Demande</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('report.statistics.transferrings') }}"><i
                            class="bi bi-graph-up"></i> Transfert</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('report.statistics.deposits') }}"><i
                            class="bi bi-graph-up"></i> Dépôt</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('report.statistics.tools') }}"><i
                            class="bi bi-graph-up"></i> Outil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('report.statistics.dollies') }}"><i
                            class="bi bi-graph-up"></i> Chariots</a>
                </li>
            </ul>
        </div>

        <!-- Dashboard -->
        <a class="nav-link active bg-primary text-white" href="{{ route('report.dashboard') }}"
            style="padding: 10px;"><i class="bi bi-speedometer2"></i> Dashboard</a>
    </div>
</div>
