<div class="container" style="background-color: #f1f1f1;">
    <div class="row">
        <!-- Search -->
        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#searchMenu" aria-expanded="true" aria-controls="searchMenu" style="padding: 10px;">
            <i class="bi bi-search"></i> {{ __('search') }}
        </a>
        <div class="collapse show" id="searchMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('buildings.index') }}"><i class="bi bi-building"></i> {{ __('building') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('rooms.index') }}"><i class="bi bi-house"></i> {{ __('room') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('shelves.index') }}"><i class="bi bi-bookshelf"></i> {{ __('shelves') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('containers.index') }}"><i class="bi bi-box"></i> {{ __('archive_container') }}</a>
                </li>
            </ul>
        </div>

        <!-- Create -->
        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#createMenu" aria-expanded="true" aria-controls="createMenu" style="padding: 10px;">
            {{ __('create') }}
        </a>
        <div class="collapse show" id="createMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('buildings.create') }}"><i class="bi bi-building"></i> {{ __('building') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('rooms.create') }}"><i class="bi bi-house"></i> {{ __('room') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('shelves.create') }}"><i class="bi bi-bookshelf"></i> {{ __('shelves') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('containers.create') }}"><i class="bi bi-archive"></i> {{ __('archive_container') }}</a>
                </li>
            </ul>
        </div>

        <!-- My Carts -->
        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#cartsMenu" aria-expanded="true" aria-controls="cartsMenu" style="padding: 10px;">
            {{ __('my_carts') }}
        </a>
        <div class="collapse show" id="cartsMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('buildings.create') }}"><i class="bi bi-building"></i> {{ __('buildings') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('rooms.create') }}"><i class="bi bi-house"></i> {{ __('rooms') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('shelves.create') }}"><i class="bi bi-bookshelf"></i> {{ __('shelves') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('containers.create') }}"><i class="bi bi-archive"></i> {{ __('archive_container') }}</a>
                </li>
            </ul>
        </div>
    </div>
</div>
