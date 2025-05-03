<div class="nav flex-column nav-pills">
    <a class="nav-link @if (Request::segment(2) == 'users') active @endif" href="{{ route('public.users.index') }}">
        <i class="bi bi-people"></i> {{ __('Users') }}
    </a>
    <a class="nav-link @if (Request::segment(2) == 'events') active @endif" href="{{ route('public.events.index') }}">
        <i class="bi bi-calendar-event"></i> {{ __('Events') }}
    </a>
    <a class="nav-link @if (Request::segment(2) == 'news') active @endif" href="{{ route('public.news.index') }}">
        <i class="bi bi-newspaper"></i> {{ __('News') }}
    </a>
    <a class="nav-link @if (Request::segment(2) == 'pages') active @endif" href="{{ route('public.pages.index') }}">
        <i class="bi bi-file-text"></i> {{ __('Pages') }}
    </a>
    <a class="nav-link @if (Request::segment(2) == 'document-requests') active @endif" href="{{ route('public.document-requests.index') }}">
        <i class="bi bi-file-earmark-text"></i> {{ __('Document Requests') }}
    </a>
    <a class="nav-link @if (Request::segment(2) == 'records') active @endif" href="{{ route('public.records.index') }}">
        <i class="bi bi-archive"></i> {{ __('Records') }}
    </a>
    <a class="nav-link @if (Request::segment(2) == 'responses') active @endif" href="{{ route('public.responses.index') }}">
        <i class="bi bi-reply"></i> {{ __('Responses') }}
    </a>
    <a class="nav-link @if (Request::segment(2) == 'feedback') active @endif" href="{{ route('public.feedback.index') }}">
        <i class="bi bi-chat-square-text"></i> {{ __('Feedback') }}
    </a>
    <a class="nav-link @if (Request::segment(2) == 'search-logs') active @endif" href="{{ route('public.search-logs.index') }}">
        <i class="bi bi-search"></i> {{ __('Search Logs') }}
    </a>
    <a class="nav-link @if (Request::segment(2) == 'chats') active @endif" href="{{ route('public.chats.index') }}">
        <i class="bi bi-chat-dots"></i> {{ __('Chats') }}
    </a>
</div>
