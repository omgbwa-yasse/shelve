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

    <!-- Mon Compte Section -->
    <div class="submenu-section">
        <div class="submenu-heading" >
            <i class="bi bi-person-circle"></i> {{ __('my_account') }}
        </div>
        <div class="submenu-content" id="accountMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('users.show', auth()->user()->id) }}">
                    <i class="bi bi-gear"></i> {{ __('my_account') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Paramètres Section -->
    <div class="submenu-section">
        <div class="submenu-heading">
            <i class="bi bi-sliders"></i> {{ __('settings') }}
        </div>
        <div class="submenu-content" id="settingsMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('settings.categories.index') }}">
                    <i class="bi bi-folder"></i> {{ __('Categories') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('settings.definitions.index') }}">
                    <i class="bi bi-gear-wide-connected"></i> {{ __('Parameters') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Autorisations et postes Section -->
    <div class="submenu-section">
        <div class="submenu-heading" >
            <i class="bi bi-people"></i> {{ __('authorizations_and_positions') }}
        </div>
        <div class="submenu-content" id="authorizationsMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('users.index') }}">
                    <i class="bi bi-person"></i> {{ __('users') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('user-organisation-role.index') }}">
                    <i class="bi bi-diagram-3"></i> {{ __('assigned_positions') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Droits et permissions Section -->
    <div class="submenu-section">
        <div class="submenu-heading" >
            <i class="bi bi-shield-lock"></i> {{ __('rights_and_permissions') }}
        </div>
        <div class="submenu-content" id="rightsMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('roles.index') }}">
                    <i class="bi bi-person-badge"></i> {{ __('roles') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('roles.create') }}">
                    <i class="bi bi-person-plus"></i> {{ __('create_role') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('role_permissions.index') }}">
                    <i class="bi bi-key"></i> {{ __('assign_permissions') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('role_permissions.create') }}">
                    <i class="bi bi-key-fill"></i> {{ __('create_permission') }}
                </a>
            </div>
        </div>
    </div>


    <!-- Courrier Section -->
    <div class="submenu-section">
        <div class="submenu-heading" >
            <i class="bi bi-envelope"></i> {{ __('mail') }}
        </div>
        <div class="submenu-content" id="mailMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-typology.index') }}">
                    <i class="bi bi-tags"></i> {{ __('typologies') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-action.index') }}">
                    <i class="bi bi-play-circle"></i> {{ __('actions') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-priority.index') }}">
                    <i class="bi bi-flag"></i> {{ __('priorities') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Répertoire Section -->
    <div class="submenu-section">
        <div class="submenu-heading" >
            <i class="bi bi-file-text"></i> {{ __('directory') }}
        </div>
        <div class="submenu-content" id="directoryMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('record-supports.index') }}">
                    <i class="bi bi-hdd"></i> {{ __('supports') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('record-statuses.index') }}">
                    <i class="bi bi-flag"></i> {{ __('statuses') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Transfert Section -->
    <div class="submenu-section">
        <div class="submenu-heading" >
            <i class="bi bi-arrow-right-circle"></i> {{ __('transfer') }}
        </div>
        <div class="submenu-content" id="transferMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('transferring-status.index') }}">
                    <i class="bi bi-flag"></i> {{ __('statuses') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Dépôt Section -->
    <div class="submenu-section">
        <div class="submenu-heading" >
            <i class="bi bi-building"></i> {{ __('deposit') }}
        </div>
        <div class="submenu-content" id="depositMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('container-status.index') }}">
                    <i class="bi bi-flag"></i> {{ __('container_statuses') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('container-property.index') }}">
                    <i class="bi bi-list-check"></i> {{ __('container_property') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Outils de gestion Section -->
    <div class="submenu-section">
        <div class="submenu-heading" >
            <i class="bi bi-gear"></i> {{ __('management_tools') }}
        </div>
        <div class="submenu-content" id="managementMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('sorts.index') }}">
                    <i class="bi bi-sort-alpha-down"></i> {{ __('retention_final_sorts') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('thesaurus.index') }}">
                    <i class="bi bi-tag"></i> {{ __('thesaurus_terms') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('thesaurus.search.index') }}">
                    <i class="bi bi-search"></i> {{ __('thesaurus_search') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('languages.index') }}">
                    <i class="bi bi-translate"></i> {{ __('languages') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('thesaurus.export-import') }}">
                    <i class="bi bi-cloud-arrow-up-down"></i> {{ __('thesaurus_export_import') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Système Section -->
    <div class="submenu-section">
        <div class="submenu-heading" >
            <i class="bi bi-cpu"></i> {{ __('system') }}
        </div>
        <div class="submenu-content" id="systemMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('backups.index')}}">
                    <i class="bi bi-save"></i> {{ __('my_backups') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('backups.create')}}">
                    <i class="bi bi-plus-square"></i> {{ __('new_backup') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="">
                    <i class="bi bi-people"></i> {{ __('ldap') }}
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
