<div class="container" style="background-color: #f1f1f1;">
    <div class="row">
        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#rechercheMenu" aria-expanded="true"
           aria-controls="rechercheMenu" style="padding: 10px;"><i class="bi bi-search"></i> {{ __('search') }}</a>

        <div class="collapse show" id="rechercheMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('slips.index') }}"><i class="bi bi-building"></i> {{ __('my_slips') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('slips-select-date') }}"><i class="bi bi-list"></i> {{ __('dates') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('slips-select-organisation') }}?categ=organisation"><i class="bi bi-list"></i> {{ __('organizations') }}</a>
                </li>
            </ul>
        </div>

        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#suiviTransfertMenu" aria-expanded="true"
           aria-controls="suiviTransfertMenu" style="padding: 10px;"><i class="bi bi-search"></i> {{ __('transfer_tracking') }}</a>

        <div class="collapse show" id="suiviTransfertMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('slips-sort') }}?categ=project"><i class="bi bi-folder"></i> {{ __('projects') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('slips-sort') }}?categ=received"><i class="bi bi-envelope-check"></i> {{ __('received') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('slips-sort') }}?categ=approved"><i class="bi bi-check-circle"></i> {{ __('approved') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('slips-sort') }}?categ=integrated"><i class="bi bi-folder-plus"></i> {{ __('integrated') }}</a>
                </li>
            </ul>
        </div>

        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#enregistrementMenu"
           aria-expanded="true" aria-controls="enregistrementMenu" style="padding: 10px;">{{ __('create') }}</a>
        <div class="collapse show" id="enregistrementMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('slips.create') }}"><i class="bi bi-building"></i> {{ __('slip') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('slips.containers.index') }}"><i class="bi bi-archive"></i> {{ __('box_chrono') }}</a>
                </li>
            </ul>
        </div>

        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#importExportMenu" aria-expanded="false"
           aria-controls="importExportMenu" style="padding: 10px;">
            <i class="bi bi-cart"></i> {{ __('import_export') }} (EAD, Excel, SEDA)
        </a>
        <div class="collapse show" id="importExportMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('slips.import.form') }}"><i class="bi bi-folder-check"></i> {{ __('import') }}</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('slips.export.form') }}" class="nav-link text-dark"><i class="bi bi-folder-check"></i> {{ __('export') }}</a>
                </li>
            </ul>
        </div>
    </div>
</div>
