<div class="submenu-container py-2">
    <!-- Google Fonts - Inter -->
    <link rel="preconnec    <!-- Notifications Section -->
    <div class="submenu-section">
        <div class="submenu-heading">
            <i class="bi bi-bell"></i> {{ __('Notifications') }}
        </div>
        <div class="submenu-content" id="notificationsMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('bulletin-boards.notifications.user') }}">
                    <i class="bi bi-person"></i> Mes Notifications
                </a>
            </div>
            @if(auth()->user() && isset(auth()->user()->current_organisation_id))
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('bulletin-boards.notifications.organisation') }}">
                    <i class="bi bi-building"></i> Notifications Organisation
                </a>
            </div>
            @endif
        </div>
    </div>ts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        .submenu-container {
            font-family: 'Inter', sans-serif;
            /*font-size: 0.1rem;*/
        }

        .submenu-heading {
            background-color: #4285f4;
            color: white;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 6px;
            font-weight: 500;
            font-size: 10px;
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

        .submenu-content { padding: 0 0 8px 12px; margin-bottom: 8px; display: block; /* Toujours visible par d�faut */ }

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

    <!-- Notifications Section -->
    <div class="submenu-section">
        <div class="submenu-heading">
            <i class="bi bi-bell"></i> {{ __('Notifications') }}
        </div>
        <div class="submenu-content" id="notificationsMenu">
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

    <!-- Recherche Section -->
    @can('viewAny', App\Models\BulletinBoard::class)
    <div class="submenu-section">
        <div class="submenu-section">
            <div class="submenu-heading" >
                <i class="bi bi-search"></i> {{ __('search') }}
            </div>
            <div class="submenu-content" id="rechercheMenu">
                <div class="submenu-item">
                    <a class="submenu-link" href="{{ route('bulletin-boards.index') }}">
                        <i class="bi bi-clipboard"></i> Mes barbillards
                    </a>
                </div>
                @can('create', App\Models\BulletinBoard::class)
                <div class="submenu-item">
                    <a class="submenu-link" href="{{ route('bulletin-boards.create') }}">
                        <i class="bi bi-clipboard-plus"></i> Nouveau
                    </a>
                </div>
                @endcan
            </div>
        </div>
    </div>
    @endcan
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

