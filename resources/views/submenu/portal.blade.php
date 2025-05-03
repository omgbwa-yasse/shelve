@php
    $currentRoute = request()->route()->getName();
@endphp

<div class="container p-2" style="background-color: #f1f1f1; font-size: 0.9rem;">
    <div class="row">
        <!-- Gestion des utilisateurs -->
        <a href="{{ route('public.users.index') }}" class="nav-link @if (Request::routeIs('public.users.*')) active @endif">
            <i class="bi bi-people"></i> {{ __('Users') }}
        </a>

        <!-- Gestion des chats -->
        <a href="{{ route('public.chats.index') }}" class="nav-link @if (Request::routeIs('public.chats.*')) active @endif">
            <i class="bi bi-chat-dots"></i> {{ __('Chats') }}
        </a>

        <!-- Gestion des événements -->
        <a href="{{ route('public.events.index') }}" class="nav-link @if (Request::routeIs('public.events.*')) active @endif">
            <i class="bi bi-calendar-event"></i> {{ __('Events') }}
        </a>

        <!-- Gestion des inscriptions aux événements -->
        <a href="{{ route('public.event-registrations.index') }}" class="nav-link @if (Request::routeIs('public.event-registrations.*')) active @endif">
            <i class="bi bi-calendar-check"></i> {{ __('Event Registrations') }}
        </a>

        <!-- Gestion des actualités -->
        <a href="{{ route('public.news.index') }}" class="nav-link @if (Request::routeIs('public.news.*')) active @endif">
            <i class="bi bi-newspaper"></i> {{ __('News') }}
        </a>

        <!-- Gestion des pages -->
        <a href="{{ route('public.pages.index') }}" class="nav-link @if (Request::routeIs('public.pages.*')) active @endif">
            <i class="bi bi-file-text"></i> {{ __('Pages') }}
        </a>

        <!-- Gestion des templates -->
        <a href="{{ route('public.templates.index') }}" class="nav-link @if (Request::routeIs('public.templates.*')) active @endif">
            <i class="bi bi-file-earmark-text"></i> {{ __('Templates') }}
        </a>

        <!-- Gestion des demandes de documents -->
        <a href="{{ route('public.document-requests.index') }}" class="nav-link @if (Request::routeIs('public.document-requests.*')) active @endif">
            <i class="bi bi-file-earmark-arrow-down"></i> {{ __('Document Requests') }}
        </a>

        <!-- Gestion des documents -->
        <a href="{{ route('public.records.index') }}" class="nav-link @if (Request::routeIs('public.records.*')) active @endif">
            <i class="bi bi-folder"></i> {{ __('Records') }}
        </a>

        <!-- Gestion des réponses -->
        <a href="{{ route('public.responses.index') }}" class="nav-link @if (Request::routeIs('public.responses.*')) active @endif">
            <i class="bi bi-reply"></i> {{ __('Responses') }}
        </a>

        <!-- Gestion des pièces jointes -->
        <a href="{{ route('public.response-attachments.index') }}" class="nav-link @if (Request::routeIs('public.response-attachments.*')) active @endif">
            <i class="bi bi-paperclip"></i> {{ __('Attachments') }}
        </a>

        <!-- Gestion des retours -->
        <a href="{{ route('public.feedback.index') }}" class="nav-link @if (Request::routeIs('public.feedback.*')) active @endif">
            <i class="bi bi-chat-square-text"></i> {{ __('Feedback') }}
        </a>

        <!-- Logs de recherche -->
        <a href="{{ route('public.search-logs.index') }}" class="nav-link @if (Request::routeIs('public.search-logs.*')) active @endif">
            <i class="bi bi-search"></i> {{ __('Search Logs') }}
        </a>
    </div>
</div>
