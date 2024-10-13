<div class="container">
    <div class="row">
        <!-- Recherche -->
        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#rechercheMenu" aria-expanded="false"
           style="padding: 10px;" aria-controls="rechercheMenu">
            <i class="bi bi-search"></i> {{ __('search') }}
        </a>
        <div class="collapse show" id="rechercheMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('records.index') }}"><i class="bi bi-list-check"></i> {{ __('my_archives') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('record-author.index') }}"><i class="bi bi-person"></i> {{ __('holders') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('record-select-date')}}"><i class="bi bi-calendar"></i> {{ __('dates') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('record-select-word')}}"><i class="bi bi-key"></i> {{ __('keywords') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('record-select-activity')}}"><i class="bi bi-briefcase"></i> {{ __('activities') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('record-select-building')}}"><i class="bi bi-building"></i> {{ __('premises') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('record-select-last')}}"><i class="bi bi-clock-history"></i> {{ __('recent') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('records.advanced.form')}}"><i class="bi bi-search"></i> {{ __('advanced') }}</a>
                </li>
            </ul>
        </div>

        <!-- Enregistrement -->
        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#enregistrementMenu"
           aria-expanded="false" style="padding: 10px;" aria-controls="enregistrementMenu">
            <i class="bi bi-journal-plus"></i> {{ __('registration') }}
        </a>
        <div class="collapse show" id="enregistrementMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('records.create') }}"><i class="bi bi-plus-square"></i> {{ __('new') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('record-author.create') }}"><i class="bi bi-plus-square"></i> {{ __('producer') }}</a>
                </li>
            </ul>
        </div>

        <!-- lifeCycle -->
        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#lifeCycleMenu" aria-expanded="false"
           aria-controls="lifeCycleMenu" style="padding: 10px;">
            <i class="bi bi-cart"></i> {{ __('life_cycle') }}
        </a>
        <div class="collapse show" id="lifeCycleMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('records.tostore')}}"><i class="bi bi-folder-check"></i> {{ __('to_transfer') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('records.toretain')}}"><i class="bi bi-folder-check"></i> {{ __('active_files') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('records.totransfer')}}"><i class="bi bi-arrow-right-square"></i> {{ __('to_deposit') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('records.toeliminate')}}"><i class="bi bi-trash"></i> {{ __('to_eliminate') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('records.tokeep')}}"><i class="bi bi-archive"></i> {{ __('to_keep') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('records.tosort')}}"><i class="bi bi-sort-down"></i> {{ __('to_sort') }}</a>
                </li>
            </ul>
        </div>

        <!-- Import / Export -->
        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#importExportMenu" aria-expanded="false"
           aria-controls="importExportMenu" style="padding: 10px;">
            <i class="bi bi-cart"></i> {{ __('import_export') }} (EAD, Excel, SEDA)
        </a>
        <div class="collapse show" id="importExportMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('records.import.form') }}"><i class="bi bi-folder-check"></i> {{ __('import') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('records.export.form') }}"><i class="bi bi-folder-check"></i> {{ __('export') }}</a>
                </li>
            </ul>
        </div>
    </div>
</div>
