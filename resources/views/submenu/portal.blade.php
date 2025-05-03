@php
    $currentRoute = request()->route()->getName();
@endphp

<div class="container p-2" style="background-color: #f1f1f1; font-size: 0.9rem;">
    <div class="row">
        <!-- Utilisateurs -->
        <a href="{{ route('public.users.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'public.users.') ? 'active' : '' }}" data-toggle="collapse" href="#usersMenu" aria-expanded="true"
        aria-controls="usersMenu" style="padding: 6px 8px; font-size: 13px; border-radius: 4px; margin-bottom: 4px;"><i class="bi bi-people"></i> {{ __('Users') }} </a>

        <div class="collapse show" id="usersMenu">
            <ul class="list-unstyled pl-2 mb-2">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="" style="padding: 3px 6px; font-size: 12.5px;"><i class="bi bi-plus-circle" style="font-size: 12px; margin-right: 5px;"></i> Ajouter Utilisateur </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="" style="padding: 3px 6px; font-size: 12.5px;"><i class="bi bi-list" style="font-size: 12px; margin-right: 5px;"></i> Liste Utilisateurs </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="" style="padding: 3px 6px; font-size: 12.5px;"><i class="bi bi-gear" style="font-size: 12px; margin-right: 5px;"></i> Paramètres Utilisateurs </a>
                </li>
            </ul>
        </div>

        <!-- Templates -->
        <a href="{{ route('public.templates.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'public.templates.') ? 'active' : '' }}" data-toggle="collapse" href="#templatesMenu" aria-expanded="true"
        aria-controls="templatesMenu" style="padding: 6px 8px; font-size: 13px; border-radius: 4px; margin-bottom: 4px;"><i class="bi bi-layout-text-window-reverse"></i> {{ __('Templates') }} </a>

        <div class="collapse show" id="templatesMenu">
            <ul class="list-unstyled pl-2 mb-2">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="" style="padding: 3px 6px; font-size: 12.5px;"><i class="bi bi-plus-circle" style="font-size: 12px; margin-right: 5px;"></i> Nouveau Template </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="" style="padding: 3px 6px; font-size: 12.5px;"><i class="bi bi-list" style="font-size: 12px; margin-right: 5px;"></i> Liste Templates </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="" style="padding: 3px 6px; font-size: 12.5px;"><i class="bi bi-gear" style="font-size: 12px; margin-right: 5px;"></i> Paramètres Templates </a>
                </li>
            </ul>
        </div>

        <!-- Enregistrements -->
        <a href="{{ route('public.records.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'public.records.') ? 'active' : '' }}" data-toggle="collapse" href="#recordsMenu" aria-expanded="true"
        aria-controls="recordsMenu" style="padding: 6px 8px; font-size: 13px; border-radius: 4px; margin-bottom: 4px;"><i class="bi bi-file-earmark"></i> {{ __('Records') }} </a>

        <div class="collapse show" id="recordsMenu">
            <ul class="list-unstyled pl-2 mb-2">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="" style="padding: 3px 6px; font-size: 12.5px;"><i class="bi bi-plus-circle" style="font-size: 12px; margin-right: 5px;"></i> Nouvel Enregistrement </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="" style="padding: 3px 6px; font-size: 12.5px;"><i class="bi bi-list" style="font-size: 12px; margin-right: 5px;"></i> Liste Enregistrements </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="" style="padding: 3px 6px; font-size: 12.5px;"><i class="bi bi-gear" style="font-size: 12px; margin-right: 5px;"></i> Paramètres Enregistrements </a>
                </li>
            </ul>
        </div>


        <!-- Événements -->
        <a href="{{ route('public.events.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'public.events.') ? 'active' : '' }}" data-toggle="collapse" href="#eventsMenu" aria-expanded="true"
        aria-controls="eventsMenu" style="padding: 6px 8px; font-size: 13px; border-radius: 4px; margin-bottom: 4px;"><i class="bi bi-calendar-event"></i> {{ __('Events') }} </a>

        <div class="collapse show" id="eventsMenu">
            <ul class="list-unstyled pl-2 mb-2">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="" style="padding: 3px 6px; font-size: 12.5px;"><i class="bi bi-plus-circle" style="font-size: 12px; margin-right: 5px;"></i> Nouvel Événement </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="" style="padding: 3px 6px; font-size: 12.5px;"><i class="bi bi-list" style="font-size: 12px; margin-right: 5px;"></i> Liste Événements </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="" style="padding: 3px 6px; font-size: 12.5px;"><i class="bi bi-gear" style="font-size: 12px; margin-right: 5px;"></i> Paramètres Événements </a>
                </li>
            </ul>
        </div>


        <!-- Pages -->
        <a href="{{ route('public.pages.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'public.pages.') ? 'active' : '' }}" data-toggle="collapse" href="#pagesMenu" aria-expanded="true"
        aria-controls="pagesMenu" style="padding: 6px 8px; font-size: 13px; border-radius: 4px; margin-bottom: 4px;"><i class="bi bi-file-text"></i> {{ __('Pages') }} </a>

        <div class="collapse show" id="pagesMenu">
            <ul class="list-unstyled pl-2 mb-2">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="" style="padding: 3px 6px; font-size: 12.5px;"><i class="bi bi-plus-circle" style="font-size: 12px; margin-right: 5px;"></i> Nouvelle Page </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="" style="padding: 3px 6px; font-size: 12.5px;"><i class="bi bi-list" style="font-size: 12px; margin-right: 5px;"></i> Liste Pages </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="" style="padding: 3px 6px; font-size: 12.5px;"><i class="bi bi-gear" style="font-size: 12px; margin-right: 5px;"></i> Paramètres Pages </a>
                </li>
            </ul>
        </div>


        <!-- Actualités -->
        <a href="{{ route('public.news.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'public.news.') ? 'active' : '' }}" data-toggle="collapse" href="#newsMenu" aria-expanded="true"
        aria-controls="newsMenu" style="padding: 6px 8px; font-size: 13px; border-radius: 4px; margin-bottom: 4px;"><i class="bi bi-newspaper"></i> {{ __('News') }} </a>

        <div class="collapse show" id="newsMenu">
            <ul class="list-unstyled pl-2 mb-2">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="" style="padding: 3px 6px; font-size: 12.5px;"><i class="bi bi-plus-circle" style="font-size: 12px; margin-right: 5px;"></i> Nouvelle Actualité </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="" style="padding: 3px 6px; font-size: 12.5px;"><i class="bi bi-list" style="font-size: 12px; margin-right: 5px;"></i> Liste Actualités </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="" style="padding: 3px 6px; font-size: 12.5px;"><i class="bi bi-gear" style="font-size: 12px; margin-right: 5px;"></i> Paramètres Actualités </a>
                </li>
            </ul>
        </div>



        <!-- Réponses -->
        <a href="{{ route('public.feedback.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'public.feedback.') ? 'active' : '' }}" data-toggle="collapse" href="#responsesMenu" aria-expanded="true"
        aria-controls="responsesMenu" style="padding: 6px 8px; font-size: 13px; border-radius: 4px; margin-bottom: 4px;"><i class="bi bi-reply"></i> {{ __('Feedback') }} </a>

        <div class="collapse show" id="responsesMenu">
            <ul class="list-unstyled pl-2 mb-2">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="" style="padding: 3px 6px; font-size: 12.5px;"><i class="bi bi-plus-circle" style="font-size: 12px; margin-right: 5px;"></i> Nouvelle Réponse </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="" style="padding: 3px 6px; font-size: 12.5px;"><i class="bi bi-list" style="font-size: 12px; margin-right: 5px;"></i> Liste demandes </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="" style="padding: 3px 6px; font-size: 12.5px;"><i class="bi bi-list" style="font-size: 12px; margin-right: 5px;"></i> Liste Réponses </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="" style="padding: 3px 6px; font-size: 12.5px;"><i class="bi bi-gear" style="font-size: 12px; margin-right: 5px;"></i> Paramètres Réponses </a>
                </li>
            </ul>
        </div>

    </div>
</div>
