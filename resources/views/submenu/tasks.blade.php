<div class="container" style="background-color: #f1f1f1;">
    <div class="row">

        <!-- Task Tracking -->
        <a class="nav-link active bg-primary text-white" data-toggle="collapse" href="#trackingMenu" aria-expanded="true" aria-controls="trackingMenu" style="padding: 10px;">
            {{ __('task') }}
        </a>
        <div class="collapse show" id="trackingMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('tasks.create') }}"><i class="bi bi-plus-circle"></i> {{ __('new task') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('tasks.index') }}"><i class="bi bi-collection"></i> {{ __('all task') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('tasks.index') }}"><i class="bi bi-hourglass-split"></i> {{ __('ongoing') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('tasks.index') }}"><i class="bi bi-check2-circle"></i> {{ __('completed') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('tasks.myTasks') }}"><i class="bi bi-person-workspace"></i> {{ __('My tasks') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('tasks.supervision') }}"><i class="bi bi-eye"></i> {{ __('supervision') }}</a>
                </li>
            </ul>
        </div>


        <!-- Task Tracking -->
        <a class="nav-link active bg-primary text-white" data-toggle="collapse" href="#trackingWorkflow" aria-expanded="true" aria-controls="trackingWorkflow" style="padding: 10px;">
            Workflow**
        </a>
        <div class="collapse show" id="trackingWorkflow">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href=""><i class="bi bi-plus-circle"></i> New</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href=""><i class="bi bi-hourglass-split"></i> En cours</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href=""><i class="bi bi-check2-circle"></i> Effectué</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href=""><i class="bi bi-eye"></i> Supervisé</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href=""><i class="bi bi-list-ul"></i> Liste</a>
                </li>
            </ul>
        </div>




        <!-- Section Projet -->
    <a class="nav-link active bg-primary text-white" data-toggle="collapse" href="#projectSection" aria-expanded="true" aria-controls="projectSection" style="padding: 10px;">
        Projet**
    </a>
    <div class="collapse show" id="projectSection">
        <ul class="list-unstyled pl-3">
            <li class="nav-item">
                <a class="nav-link text-dark" href=""><i class="bi bi-clipboard-data"></i> Tableau de bord</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-dark" href=""><i class="bi bi-plus-circle"></i> Nouveau projet</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-dark" href=""><i class="bi bi-list-check"></i> Liste des projets</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-dark" href=""><i class="bi bi-archive"></i> Archives</a>
            </li>
        </ul>
    </div>


    </div>
</div>
