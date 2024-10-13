<div class="container">
    <div class="row">
        <!-- Communications -->
        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#communicationsMenu" aria-expanded="true" aria-controls="communicationsMenu" style="padding: 10px;">
            <i class="bi bi-search"></i> {{ __('communications') }}
        </a>
        <div class="collapse show" id="communicationsMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('transactions.index')}}"><i class="bi bi-inbox"></i> {{ __('view_all') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('communications-sort')}}?categ=return-effective"><i class="bi bi-inbox"></i> {{ __('returned') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('communications-sort')}}?categ=unreturn"><i class="bi bi-inbox"></i> {{ __('without_return') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('communications-sort')}}?categ=not-return"><i class="bi bi-inbox"></i> {{ __('not_returned') }}</a>
                </li>
            </ul>
        </div>

        <!-- Reservations -->
        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#reservationsMenu" aria-expanded="true" aria-controls="reservationsMenu" style="padding: 10px;">
            <i class="bi bi-search"></i> {{ __('reservations') }}
        </a>
        <div class="collapse show" id="reservationsMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('reservations.index')}}"><i class="bi bi-inbox"></i> {{ __('view_all') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('reservations-sort')}}?categ=InProgess"><i class="bi bi-inbox"></i> {{ __('under_review') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('reservations-sort')}}?categ=approved"><i class="bi bi-inbox"></i> {{ __('approved') }}</a>
                </li>
            </ul>
        </div>

        <!-- Add -->
        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#addMenu" aria-expanded="true" aria-controls="addMenu" style="padding: 10px;">
            <i class="bi bi-journal-plus"></i> {{ __('add') }}
        </a>
        <div class="collapse show" id="addMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('transactions.create')}}"><i class="bi bi-inbox"></i> {{ __('add_communication') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('reservations.create')}}"><i class="bi bi-envelope"></i> {{ __('add_reservation') }}</a>
                </li>
            </ul>
        </div>
    </div>
</div>
