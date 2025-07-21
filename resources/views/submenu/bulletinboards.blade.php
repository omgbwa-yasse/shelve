<div class="bulletinboards-submenu" id="bulletinBoardsMenu">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        .bulletinboards-submenu {
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
        }

        .bulletinboards-submenu .submenu-heading {
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
            user-select: none;
        }

        .bulletinboards-submenu .submenu-heading:hover {
            background-color: #3367d6;
        }

        .bulletinboards-submenu .submenu-heading i {
            margin-right: 8px;
            font-size: 14px;
        }

        .bulletinboards-submenu .submenu-section-content {
            padding: 0 0 8px 12px;
            margin-bottom: 8px;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .bulletinboards-submenu .submenu-item {
            margin-bottom: 2px;
        }

        .bulletinboards-submenu .submenu-link {
            display: flex;
            align-items: center;
            padding: 4px 8px;
            color: #202124;
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.2s ease;
            font-size: 12.5px;
        }

        .bulletinboards-submenu .submenu-link:hover {
            background-color: #f1f3f4;
            color: #4285f4;
            text-decoration: none;
        }

        .bulletinboards-submenu .submenu-link i {
            margin-right: 8px;
            color: #5f6368;
            font-size: 13px;
        }

        .bulletinboards-submenu .submenu-link:hover i {
            color: #4285f4;
        }

        .bulletinboards-submenu .add-section .submenu-heading {
            background-color: #34a853;
        }

        .bulletinboards-submenu .add-section .submenu-heading:hover {
            background-color: #188038;
        }

        /* Animation de collapse plus fluide */
        .bulletinboards-submenu .submenu-section-content.collapsed {
            max-height: 0 !important;
            padding-top: 0;
            padding-bottom: 0;
            margin-bottom: 0;
            opacity: 0;
        }

        .bulletinboards-submenu .submenu-heading::after {
            content: '▼';
            margin-left: auto;
            font-size: 12px;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .bulletinboards-submenu .submenu-heading.collapsed::after {
            transform: rotate(-90deg);
        }

        /* État par défaut - tout ouvert */
        .bulletinboards-submenu .submenu-section-content {
            max-height: 300px;
            opacity: 1;
        }
    </style>

    <!-- Notifications Section -->
    <div class="submenu-section">
        <div class="submenu-heading">
            <i class="bi bi-bell"></i> {{ __('Notifications') }}
        </div>
        <div class="submenu-section-content" id="notificationsSection">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('bulletin-boards.notifications.user') }}">
                    <i class="bi bi-person"></i> Mes Notifications
                </a>
            </div>
            @if(auth()->user() && auth()->user()->current_organisation_id)
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('bulletin-boards.notifications.organisation') }}">
                    <i class="bi bi-building"></i> Notifications Organisation
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Gestion Section -->
    @can('viewAny', App\Models\BulletinBoard::class)
    <div class="submenu-section">
        <div class="submenu-heading">
            <i class="bi bi-clipboard"></i> {{ __('Gestion') }}
        </div>
        <div class="submenu-section-content" id="gestionSection">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('bulletin-boards.index') }}">
                    <i class="bi bi-list-ul"></i> {{ __('view_all') }}
                </a>
            </div>
        </div>
    </div>
    @endcan

    <!-- Add Section -->
    @can('create', App\Models\BulletinBoard::class)
    <div class="submenu-section add-section">
        <div class="submenu-heading">
            <i class="bi bi-plus-circle"></i> {{ __('add') }}
        </div>
        <div class="submenu-section-content" id="addSection">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('bulletin-boards.create') }}">
                    <i class="bi bi-clipboard-plus"></i> {{ __('Nouveau bulletin') }}
                </a>
            </div>
        </div>
    </div>
    @endcan
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Bulletin Boards submenu initialized');

    // Sélectionner uniquement les éléments de notre sous-menu
    const menuContainer = document.getElementById('bulletinBoardsMenu');
    if (!menuContainer) {
        console.error('Bulletin Boards menu container not found');
        return;
    }

    const headings = menuContainer.querySelectorAll('.submenu-heading');
    console.log('Found headings:', headings.length);

    headings.forEach(function(heading) {
        heading.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const content = this.nextElementSibling;
            console.log('Clicked heading:', this.textContent.trim());
            console.log('Content element:', content);

            if (content && content.classList.contains('submenu-section-content')) {
                // Toggle les classes
                const isCollapsed = content.classList.contains('collapsed');

                if (isCollapsed) {
                    content.classList.remove('collapsed');
                    this.classList.remove('collapsed');
                    console.log('Expanded section');
                } else {
                    content.classList.add('collapsed');
                    this.classList.add('collapsed');
                    console.log('Collapsed section');
                }
            } else {
                console.error('Content element not found or invalid');
            }
        });
    });

    // S'assurer que tous les menus sont ouverts par défaut
    const allContents = menuContainer.querySelectorAll('.submenu-section-content');
    const allMenuHeadings = menuContainer.querySelectorAll('.submenu-heading');

    allContents.forEach(function(content) {
        content.classList.remove('collapsed');
    });

    allMenuHeadings.forEach(function(heading) {
        heading.classList.remove('collapsed');
    });

    console.log('All sections opened by default');
});
</script>

