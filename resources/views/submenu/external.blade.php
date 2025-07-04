<div class="submenu-container py-2">
    @php
    use App\Helpers\SubmenuPermissions;
    @endphp

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

        .submenu-content { padding: 0 0 8px 12px; margin-bottom: 8px; display: block; /* Toujours visible par défaut */ }

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

        .submenu-link.active {
            background-color: #e8f0fe;
            color: #1a73e8;
        }

        .submenu-link.active i {
            color: #1a73e8;
        }

        .submenu-badge {
            background-color: #e8f0fe;
            color: #1a73e8;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: auto;
        }

        .submenu-divider {
            height: 1px;
            background-color: #e0e0e0;
            margin: 8px 0;
        }
    </style>

    <!-- Organisations -->
    <div class="submenu-section mb-3">
        <div class="submenu-heading" data-toggle="collapse" data-target="#organizationsSubmenu">
            <i class="bi bi-building"></i>
            Organisations externes
        </div>
        <div class="submenu-content" id="organizationsSubmenu">
            <div class="submenu-item">
                <a class="submenu-link {{ Request::routeIs('external.organizations.index') ? 'active' : '' }}" href="{{ route('external.organizations.index') }}">
                    <i class="bi bi-list"></i> Liste des organisations
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link {{ Request::routeIs('external.organizations.create') ? 'active' : '' }}" href="{{ route('external.organizations.create') }}">
                    <i class="bi bi-plus-circle"></i> Nouvelle organisation
                </a>
            </div>
        </div>
    </div>

    <!-- Contacts -->
    <div class="submenu-section mb-3">
        <div class="submenu-heading" data-toggle="collapse" data-target="#contactsSubmenu">
            <i class="bi bi-person"></i>
            Contacts externes
        </div>
        <div class="submenu-content" id="contactsSubmenu">
            <div class="submenu-item">
                <a class="submenu-link {{ Request::routeIs('external.contacts.index') ? 'active' : '' }}" href="{{ route('external.contacts.index') }}">
                    <i class="bi bi-list"></i> Liste des contacts
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link {{ Request::routeIs('external.contacts.create') ? 'active' : '' }}" href="{{ route('external.contacts.create') }}">
                    <i class="bi bi-plus-circle"></i> Nouveau contact
                </a>
            </div>
        </div>
    </div>

    <!-- Retour -->
    <div class="submenu-section mb-3">
        <div class="submenu-content">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ url('/') }}">
                    <i class="bi bi-arrow-left"></i> Retour à l'accueil
                </a>
            </div>
        </div>
    </div>

    <script>
        // Gérer l'état des menus déroulants
        document.addEventListener('DOMContentLoaded', function() {
            let headings = document.querySelectorAll('.submenu-heading');

            headings.forEach(heading => {
                heading.addEventListener('click', function() {
                    let target = document.querySelector(this.getAttribute('data-target'));
                    if (target.style.display === 'none') {
                        target.style.display = 'block';
                    } else {
                        target.style.display = 'none';
                    }
                });
            });
        });
    </script>
</div>
