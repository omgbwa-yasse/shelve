<div class="submenu-container py-2">
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        .submenu-container {
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
        }

        .submenu-heading {
            background-color: #4285f4;
            color: white;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 6px;
            font-weight: 500;
            font-size: 13px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .submenu-heading:hover {
            background-color: #3367d6;
        }

        .submenu-heading i {
            margin-right: 8px;
            font-size: 14px;
        }

        .submenu-content {
            padding: 0 0 8px 12px;
            margin-bottom: 8px;
            display: block; /* Toujours visible par défaut */
        }

        .submenu-item {
            margin-bottom: 2px;
        }

        .submenu-link {
            display: flex;
            align-items: center;
            padding: 4px 8px;
            color: #202124;
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.2s ease;
            font-size: 12.5px;
        }

        .submenu-link:hover {
            background-color: #f1f3f4;
            color: #4285f4;
            text-decoration: none;
        }

        .submenu-link i {
            margin-right: 8px;
            color: #5f6368;
            font-size: 13px;
        }

        .submenu-link:hover i {
            color: #4285f4;
        }

        .add-section .submenu-heading {
            background-color: #34a853;
        }

        .add-section .submenu-heading:hover {
            background-color: #188038;
        }

        /* Styles spécifiques pour la section Public */
        .public-section .submenu-heading {
            background-color: #ff6b35;
        }

        .public-section .submenu-heading:hover {
            background-color: #e55a2b;
        }

        .public-management-section .submenu-heading {
            background-color: #2196f3;
        }

        .public-management-section .submenu-heading:hover {
            background-color: #1976d2;
        }

        .public-content-section .submenu-heading {
            background-color: #4caf50;
        }

        .public-content-section .submenu-heading:hover {
            background-color: #388e3c;
        }

        .public-interaction-section .submenu-heading {
            background-color: #9c27b0;
        }

        .public-interaction-section .submenu-heading:hover {
            background-color: #7b1fa2;
        }

        /* Style pour les sections collapsibles */
        .submenu-content.collapsed {
            display: none;
        }

        .submenu-heading::after {
            content: '';
            margin-left: auto;
            font-family: 'bootstrap-icons';
            font-size: 12px;
            transition: transform 0.2s ease;
        }

        .submenu-heading.collapsed::after {
            transform: rotate(-90deg);
        }
    </style>

    <!-- Dashboard et statistiques Section -->

    <div class="submenu-section public-dashboard-section">
        <div class="submenu-heading">
            <i class="bi bi-graph-up"></i> {{ __('dashboard_statistics') }}
        </div>
        <div class="submenu-content" id="publicDashboardMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('public.dashboard') }}">
                    <i class="bi bi-grid-1x2-fill"></i> {{ __('dashboard') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('public.statistics') }}">
                    <i class="bi bi-bar-chart-line-fill"></i> {{ __('statistics') }}
                </a>
            </div>
        </div>
    </div>


    <!-- Gestion des Utilisateurs Publics Section -->

    <div class="submenu-section public-section">
        <div class="submenu-heading">
            <i class="bi bi-people"></i> {{ __('public_users') }}
        </div>
        <div class="submenu-content" id="publicUsersMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('public.users.index') }}">
                    <i class="bi bi-person-lines-fill"></i> {{ __('users_list') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('public.users.create') }}">
                    <i class="bi bi-person-plus"></i> {{ __('add_user') }}
                </a>
            </div>
        </div>
    </div>


    <!-- Contenu Public Section -->

    <div class="submenu-section public-content-section">
        <div class="submenu-heading">
            <i class="bi bi-newspaper"></i> {{ __('public_content') }}
        </div>
        <div class="submenu-content" id="publicContentMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('public.news.index') }}">
                    <i class="bi bi-journal-text"></i> {{ __('news') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('public.pages.index') }}">
                    <i class="bi bi-file-earmark-text"></i> {{ __('pages') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('public.templates.index') }}">
                    <i class="bi bi-layout-text-window"></i> {{ __('templates') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('public.events.index') }}">
                    <i class="bi bi-calendar-event"></i> {{ __('events') }}
                </a>
            </div>
        </div>
    </div>


    <!-- Documents et Archives Section -->

    <div class="submenu-section public-management-section">
        <div class="submenu-heading">
            <i class="bi bi-archive"></i> {{ __('documents_archives') }}
        </div>
        <div class="submenu-content" id="publicDocumentsMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('public.records.index') }}">
                    <i class="bi bi-folder"></i> {{ __('records') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('public.document-requests.index') }}">
                    <i class="bi bi-file-earmark-arrow-down"></i> {{ __('document_requests') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('public.responses.index') }}">
                    <i class="bi bi-reply"></i> {{ __('responses') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('public.response-attachments.index') }}">
                    <i class="bi bi-paperclip"></i> {{ __('response_attachments') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Interaction et Communication Section -->

    <div class="submenu-section public-interaction-section">
        <div class="submenu-heading">
            <i class="bi bi-chat-square-dots"></i> {{ __('interaction_communication') }}
        </div>
        <div class="submenu-content" id="publicInteractionMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('public.chats.index') }}">
                    <i class="bi bi-chat-left-text"></i> {{ __('chats') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('public.chat-participants.index') }}">
                    <i class="bi bi-people-fill"></i> {{ __('chat_participants') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('public.event-registrations.index') }}">
                    <i class="bi bi-calendar-check"></i> {{ __('event_registrations') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('public.feedback.index') }}">
                    <i class="bi bi-star-fill"></i> {{ __('feedback') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('public.search-logs.index') }}">
                    <i class="bi bi-search"></i> {{ __('search_logs') }}
                </a>
            </div>
        </div>
    </div>


</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fonctionnalité de collapse optionnelle pour les sous-menus
    const headings = document.querySelectorAll('.submenu-heading');

    headings.forEach(function(heading) {
        heading.addEventListener('click', function() {
            const content = this.nextElementSibling;

            if (content && content.classList.contains('submenu-content')) {
                // Toggle la classe collapsed
                content.classList.toggle('collapsed');
                this.classList.toggle('collapsed');
            }
        });
    });
});
</script>
