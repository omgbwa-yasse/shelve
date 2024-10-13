<div class="container" style="background-color: #f1f1f1;">
    <div class="row">
        <!-- Search -->
        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#searchMenu" aria-expanded="true" aria-controls="searchMenu" style="padding: 10px;">
            <i class="bi bi-search"></i> {{ __('search') }}
        </a>
        <div class="collapse show" id="searchMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('dolly.index') }}"><i class="bi bi-cart3"></i> {{ __('all_carts') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('dollies-sort')}}?categ=mail"><i class="bi bi-cart3"></i> {{ __('mail') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('dollies-sort')}}?categ=record"><i class="bi bi-cart3"></i> {{ __('archives') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('dollies-sort')}}?categ=communication"><i class="bi bi-cart3"></i> {{ __('communication') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('dollies-sort')}}?categ=room"><i class="bi bi-cart3"></i> {{ __('room') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('dollies-sort')}}?categ=shelf"><i class="bi bi-cart3"></i> {{ __('shelf') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('dollies-sort')}}?categ=container"><i class="bi bi-cart3"></i> {{ __('archive_boxes') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('dollies-sort')}}?categ=slip_record"><i class="bi bi-cart3"></i> {{ __('archives_transfer') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('dollies-sort')}}?categ=slip"><i class="bi bi-cart3"></i> {{ __('transfer') }}</a>
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
                    <a class="nav-link text-dark" href="{{ route('dolly.create') }}"><i class="bi bi-cart3"></i> {{ __('cart') }}</a>
                </li>
            </ul>
        </div>
    </div>
</div>
