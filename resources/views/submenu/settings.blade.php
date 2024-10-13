<div class="container" style="background-color: #f1f1f1;">
    <div class="row">
        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#accountMenu" aria-expanded="true"
           aria-controls="accountMenu" style="padding: 10px;"><i class="bi bi-people"></i> {{ __('my_account') }}</a>
        <div class="collapse show" id="accountMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('users.show', auth()->user()->id) }}"><i class="bi bi-gear"></i> {{ __('my_account') }}</a>
                </li>
            </ul>
        </div>

        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#authorizationsMenu" aria-expanded="true"
           aria-controls="authorizationsMenu" style="padding: 10px;"><i class="bi bi-people"></i> {{ __('authorizations_and_positions') }}</a>
        <div class="collapse show" id="authorizationsMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('users.index') }}"><i class="bi bi-gear"></i> {{ __('users') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('user-organisation-role.index') }}"><i class="bi bi-gear"></i> {{ __('assigned_positions') }}</a>
                </li>
            </ul>
        </div>

        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#rightsMenu" aria-expanded="true"
           aria-controls="rightsMenu" style="padding: 10px;"><i class="bi bi-people"></i> {{ __('rights_and_permissions') }}</a>
        <div class="collapse show" id="rightsMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('roles.index') }}"><i class="bi bi-gear"></i> {{ __('roles') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('role_permissions.index') }}"><i class="bi bi-gear"></i> {{ __('assign_permissions') }}</a>
                </li>
            </ul>
        </div>

        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#tasksMenu" aria-expanded="true"
           aria-controls="tasksMenu" style="padding: 10px;"><i class="bi bi-people"></i> {{ __('tasks') }}</a>
        <div class="collapse show" id="tasksMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('taskstatus.index') }}"><i class="bi bi-gear"></i> {{ __('task_types') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('tasktype.index') }}"><i class="bi bi-gear"></i> {{ __('task_statuses') }}</a>
                </li>
            </ul>
        </div>

        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#mailMenu" aria-expanded="true"
           aria-controls="mailMenu" style="padding: 10px;"><i class="bi bi-envelope"></i> {{ __('mail') }}</a>
        <div class="collapse show" id="mailMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('mail-typology.index') }}"><i class="bi bi-gear"></i> {{ __('typologies') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('mail-action.index') }}"><i class="bi bi-gear"></i> {{ __('actions') }}</a>
                </li>
            </ul>
        </div>

        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#directoryMenu" aria-expanded="true"
           aria-controls="directoryMenu" style="padding: 10px;"><i class="bi bi-file-text"></i> {{ __('directory') }}</a>
        <div class="collapse show" id="directoryMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('record-supports.index') }}"><i class="bi bi-gear"></i> {{ __('supports') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('record-statuses.index') }}"><i class="bi bi-gear"></i> {{ __('statuses') }}</a>
                </li>
            </ul>
        </div>

        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#transferMenu" aria-expanded="true"
           aria-controls="transferMenu" style="padding: 10px;"><i class="bi bi-newspaper"></i> {{ __('transfer') }}</a>
        <div class="collapse show" id="transferMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('transferring-status.index') }}"><i class="bi bi-gear"></i> {{ __('statuses') }}</a>
                </li>
            </ul>
        </div>

        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#communicationMenu" aria-expanded="true"
           aria-controls="communicationMenu" style="padding: 10px;"><i class="bi bi-newspaper"></i> {{ __('communication') }}</a>
        <div class="collapse show" id="communicationMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('communication-status.index') }}"><i class="bi bi-gear"></i> {{ __('communication_status') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('reservation-status.index') }}"><i class="bi bi-gear"></i> {{ __('reservation_status') }}</a>
                </li>
            </ul>
        </div>

        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#depositMenu" aria-expanded="true"
           aria-controls="depositMenu" style="padding: 10px;"><i class="bi bi-building"></i> {{ __('deposit') }}</a>
        <div class="collapse show" id="depositMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('container-status.index') }}"><i class="bi bi-gear"></i> {{ __('container_statuses') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('container-property.index') }}"><i class="bi bi-gear"></i> {{ __('container_property') }}</a>
                </li>
            </ul>
        </div>

        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#managementMenu" aria-expanded="true"
           aria-controls="managementMenu" style="padding: 10px;"><i class="bi bi-gear"></i> {{ __('management_tools') }}</a>
        <div class="collapse show" id="managementMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('sorts.index') }}"><i class="bi bi-gear"></i> {{ __('retention_final_sorts') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('languages.index') }}"><i class="bi bi-gear"></i> {{ __('thesaurus_languages') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('term-categories.index') }}"><i class="bi bi-gear"></i> {{ __('thesaurus_categories') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('term-equivalent-types.index') }}"><i class="bi bi-gear"></i> {{ __('thesaurus_equivalents') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('term-types.index') }}"><i class="bi bi-gear"></i> {{ __('thesaurus_types') }}</a>
                </li>
            </ul>
        </div>

        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#systemMenu" aria-expanded="true"
           aria-controls="systemMenu" style="padding: 10px;"><i class="bi bi-gear"></i> {{ __('system') }}</a>
        <div class="collapse show" id="systemMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('backups.index')}}"><i class="bi bi-gear"></i> {{ __('my_backups') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('backups.create')}}"><i class="bi bi-gear"></i> {{ __('new_backup') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href=""><i class="bi bi-gear"></i> {{ __('ldap') }}</a>
                </li>
            </ul>
        </div>
    </div>
</div>
