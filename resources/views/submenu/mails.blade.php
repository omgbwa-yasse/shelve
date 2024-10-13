<div class="container" style="background-color: #f1f1f1;">
    <div class="row">
        <!-- Recherche -->
        <a class="nav-link active bg-primary text-white" data-toggle="collapse" href="#rechercheMenu" aria-expanded="true"
           aria-controls="rechercheMenu" style="padding: 10px;"><i class="bi bi-search"></i> {{ __('search') }}</a>

        <div class="collapse show" id="rechercheMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('mails.index') }}"><i class="bi bi-inbox"></i> {{ __('my_mails') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('mail-received.index') }}"><i class="bi bi-inbox"></i> {{ __('received_mails') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('mail-send.index') }}"><i class="bi bi-envelope"></i> {{ __('sent_mails') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('mails.archived') }}"><i class="bi bi-inbox"></i> {{ __('archived_mails') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('mail-author.index') }}"><i class="bi bi-envelope"></i> {{ __('producers') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('typologies.index') }}"><i class="bi bi-tags"></i> {{ __('typologies') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('mail-select-date')}}"><i class="bi bi-calendar"></i> {{ __('dates') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('mail-container.index') }}"><i class="bi bi-archive"></i> {{ __('archive_boxes') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('batch.index') }}"><i class="bi bi-inbox"></i> {{ __('my_paraphers') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('batch-received.index') }}"><i class="bi bi-inbox"></i> {{ __('received_paraphers') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('batch-send.index') }}"><i class="bi bi-inbox"></i> {{ __('sent_paraphers') }}</a>
                </li>
            </ul>
        </div>

        <a class="nav-link active bg-primary rounded-2 text-white" data-toggle="collapse" href="#enregistrementMenu"
           aria-expanded="true" aria-controls="enregistrementMenu" style="padding: 10px;">{{ __('case_follow_up') }}</a>

        <div class="collapse show" id="enregistrementMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('mails.feedback')}}?type=true&?deadline=available"><i class="bi bi-inbox"></i> {{ __('expected_returns') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('mails.feedback')}}?type=true&deadline=exceeded"><i class="bi bi-bookmark-check"></i>{{ __('overdue_returns') }}</a>
                </li>
            </ul>
        </div>

        <!-- Créer -->
        <a class="nav-link active bg-primary text-white" data-toggle="collapse" href="#creerMenu" aria-expanded="true"
           aria-controls="creerMenu" style="padding: 10px;">{{ __('create') }}</a>

        <div class="collapse show" id="creerMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('mails.create') }}"><i class="bi bi-inbox"></i> {{ __('mail') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('batch.create') }}"><i class="bi bi-bookmark-check"></i> {{ __('parapher') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('mail-author.create') }}"><i class="bi bi-people"></i> {{ __('producer') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('mail-container.create') }}"><i class="bi bi-archive"></i> {{ __('box_chrono') }}</a>
                </li>
            </ul>
        </div>

        <!-- Courrier -->
        <a class="nav-link active bg-primary text-white" data-toggle="collapse" href="#courrierMenu" aria-expanded="true"
           aria-controls="courrierMenu" style="padding: 10px;">{{ __('mail') }}</a>

        <div class="collapse show" id="courrierMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('mails.inprogress') }}"><i class="bi bi-inbox"></i> {{ __('to_receive') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('mail-received.create') }}"><i class="bi bi-inbox"></i> {{ __('receive') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('mail-send.create') }}"><i class="bi bi-envelope"></i> {{ __('send') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('mail-archiving.create') }}"><i class="bi bi-archive"></i> {{ __('archive') }} </a>
                </li>
            </ul>
        </div>

        <!-- Parapheur -->
        <a class="nav-link active bg-primary text-white" data-toggle="collapse" href="#parapheurMenu" aria-expanded="true"
           aria-controls="parapheurMenu" style="padding: 10px;">{{ __('parapher') }}</a>

        <div class="collapse show" id="parapheurMenu">
            <ul class="list-unstyled pl-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('batch-send.create') }}"><i class="bi bi-inbox"></i> {{ __('send') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('batch-received.create') }}"><i class="bi bi-inbox"></i> {{ __('receive') }}</a>
                </li>
            </ul>
        </div>
    </div>
</div>
