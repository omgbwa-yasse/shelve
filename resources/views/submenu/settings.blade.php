<div class="submenu-container py-2 settings-module">
    <!-- Styles partagés via _submenu.scss -->

    <!-- Mon Compte Section -->
    <div class="submenu-section settings-section">
        <div class="submenu-heading settings-heading" >
            <i class="bi bi-person-circle"></i> {{ __('my_account') }}
        </div>
        <div class="submenu-content" id="accountMenu">
            <div class="submenu-item">
                <a class="submenu-link settings-link" href="{{ route('users.show', auth()->user()->id) }}">
                    <i class="bi bi-gear"></i> {{ __('my_account') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Paramètres Section -->
    <div class="submenu-section settings-section">
        <div class="submenu-heading settings-heading">
            <i class="bi bi-sliders"></i> {{ __('settings') }}
        </div>
        <div class="submenu-content" id="settingsMenu">
            <div class="submenu-item">
                <a class="submenu-link settings-link" href="{{ route('settings.categories.index') }}">
                    <i class="bi bi-folder"></i> {{ __('Categories') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link settings-link" href="{{ route('settings.definitions.index') }}">
                    <i class="bi bi-gear-wide-connected"></i> {{ __('Parameters') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Autorisations et postes Section -->
    <div class="submenu-section settings-section">
        <div class="submenu-heading settings-heading" >
            <i class="bi bi-people"></i> {{ __('authorizations_and_positions') }}
        </div>
        <div class="submenu-content" id="authorizationsMenu">
            <div class="submenu-item">
                <a class="submenu-link settings-link" href="{{ route('users.index') }}">
                    <i class="bi bi-person"></i> {{ __('users') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link settings-link" href="{{ route('user-organisation-role.index') }}">
                    <i class="bi bi-diagram-3"></i> {{ __('assigned_positions') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Droits et permissions Section -->
    <div class="submenu-section settings-section">
        <div class="submenu-heading settings-heading" >
            <i class="bi bi-shield-lock"></i> {{ __('rights_and_permissions') }}
        </div>
        <div class="submenu-content" id="rightsMenu">
            <div class="submenu-item">
                <a class="submenu-link settings-link" href="{{ route('roles.index') }}">
                    <i class="bi bi-person-badge"></i> {{ __('roles') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link settings-link" href="{{ route('roles.create') }}">
                    <i class="bi bi-person-plus"></i> {{ __('create_role') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link settings-link" href="{{ route('role_permissions.index') }}">
                    <i class="bi bi-key"></i> {{ __('assign_permissions') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link settings-link" href="{{ route('role_permissions.create') }}">
                    <i class="bi bi-key-fill"></i> {{ __('create_permission') }}
                </a>
            </div>
        </div>
    </div>


    <!-- Courrier Section -->
    <div class="submenu-section settings-section">
        <div class="submenu-heading settings-heading" >
            <i class="bi bi-envelope"></i> {{ __('mail') }}
        </div>
        <div class="submenu-content" id="mailMenu">
            <div class="submenu-item">
                <a class="submenu-link settings-link" href="{{ route('mail-typology.index') }}">
                    <i class="bi bi-tags"></i> {{ __('typologies') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link settings-link" href="{{ route('mail-action.index') }}">
                    <i class="bi bi-play-circle"></i> {{ __('actions') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link settings-link" href="{{ route('mail-priority.index') }}">
                    <i class="bi bi-flag"></i> {{ __('priorities') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Répertoire Section -->
    <div class="submenu-section settings-section">
        <div class="submenu-heading settings-heading" >
            <i class="bi bi-file-text"></i> {{ __('directory') }}
        </div>
        <div class="submenu-content" id="directoryMenu">
            <div class="submenu-item">
                <a class="submenu-link settings-link" href="{{ route('record-supports.index') }}">
                    <i class="bi bi-hdd"></i> {{ __('supports') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link settings-link" href="{{ route('record-statuses.index') }}">
                    <i class="bi bi-flag"></i> {{ __('statuses') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Transfert Section -->
    <div class="submenu-section settings-section">
        <div class="submenu-heading settings-heading" >
            <i class="bi bi-arrow-right-circle"></i> {{ __('transfer') }}
        </div>
        <div class="submenu-content" id="transferMenu">
            <div class="submenu-item">
                <a class="submenu-link settings-link" href="{{ route('transferring-status.index') }}">
                    <i class="bi bi-flag"></i> {{ __('statuses') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Dépôt Section -->
    <div class="submenu-section settings-section">
        <div class="submenu-heading settings-heading" >
            <i class="bi bi-building"></i> {{ __('deposit') }}
        </div>
        <div class="submenu-content" id="depositMenu">
            <div class="submenu-item">
                <a class="submenu-link settings-link" href="{{ route('container-status.index') }}">
                    <i class="bi bi-flag"></i> {{ __('container_statuses') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link settings-link" href="{{ route('container-property.index') }}">
                    <i class="bi bi-list-check"></i> {{ __('container_property') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Intelligence Artificielle Section -->
    <div class="submenu-section settings-section">
        <div class="submenu-heading settings-heading" >
            <i class="bi bi-robot"></i> {{ __('intelligence') }}
        </div>
        <div class="submenu-content" id="intelligenceMenu">
            <div class="submenu-item">
                <a class="submenu-link settings-link" href="{{ route('settings.prompts.index') }}">
                    <i class="bi bi-chat-square-text"></i> {{ __('prompts') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Outils de gestion Section -->
    <div class="submenu-section settings-section">
        <div class="submenu-heading settings-heading" >
            <i class="bi bi-gear"></i> {{ __('management_tools') }}
        </div>
        <div class="submenu-content" id="managementMenu">
            <div class="submenu-item">
                <a class="submenu-link settings-link" href="{{ route('sorts.index') }}">
                    <i class="bi bi-sort-alpha-down"></i> {{ __('retention_final_sorts') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link settings-link" href="{{ route('thesaurus.index') }}">
                    <i class="bi bi-tag"></i> {{ __('thesaurus_terms') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link settings-link" href="{{ route('thesaurus.search.index') }}">
                    <i class="bi bi-search"></i> {{ __('thesaurus_search') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link settings-link" href="{{ route('languages.index') }}">
                    <i class="bi bi-translate"></i> {{ __('languages') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link settings-link" href="{{ route('thesaurus.export-import') }}">
                    <i class="bi bi-cloud-arrow-up-down"></i> {{ __('thesaurus_export_import') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Système Section -->
    <div class="submenu-section settings-section">
        <div class="submenu-heading settings-heading" >
            <i class="bi bi-cpu"></i> {{ __('system') }}
        </div>
        <div class="submenu-content" id="systemMenu">
            <div class="submenu-item">
                <a class="submenu-link settings-link" href="{{ route('system.updates.index')}}">
                    <i class="bi bi-arrow-clockwise"></i> {{ __('system_updates') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link settings-link" href="{{ route('backups.index')}}">
                    <i class="bi bi-save"></i> {{ __('my_backups') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link settings-link" href="{{ route('backups.create')}}">
                    <i class="bi bi-plus-square"></i> {{ __('new_backup') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link settings-link" href="">
                    <i class="bi bi-people"></i> {{ __('ldap') }}
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.settings-module {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin: 1rem 0;
}

.settings-section {
    margin-bottom: 1.5rem;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.settings-section:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.settings-heading {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    padding: 1rem 1.5rem;
    font-weight: 600;
    font-size: 1.1rem;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.settings-heading:hover {
    background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
    transform: translateY(-1px);
}

.settings-heading i {
    font-size: 1.2rem;
    opacity: 0.9;
}

.settings-heading.collapsed {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
}

.submenu-content {
    padding: 0;
    background: white;
    transition: all 0.3s ease;
}

.submenu-content.collapsed {
    max-height: 0;
    overflow: hidden;
    padding: 0;
}

.submenu-item {
    border-bottom: 1px solid #f1f3f4;
    transition: all 0.2s ease;
}

.submenu-item:last-child {
    border-bottom: none;
}

.submenu-item:hover {
    background: #f8f9fa;
}

.settings-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    color: #495057;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
}

.settings-link:hover {
    color: #007bff;
    background: #f8f9fa;
    border-left-color: #007bff;
    text-decoration: none;
    transform: translateX(4px);
}

.settings-link i {
    font-size: 1.1rem;
    width: 20px;
    text-align: center;
    opacity: 0.7;
    transition: all 0.2s ease;
}

.settings-link:hover i {
    opacity: 1;
    transform: scale(1.1);
}

/* Responsive design */
@media (max-width: 768px) {
    .settings-module {
        padding: 1rem;
        margin: 0.5rem 0;
    }
    
    .settings-heading {
        padding: 0.75rem 1rem;
        font-size: 1rem;
    }
    
    .settings-link {
        padding: 0.75rem 1rem;
    }
}

/* Animation for section expansion */
@keyframes slideDown {
    from {
        max-height: 0;
        opacity: 0;
    }
    to {
        max-height: 500px;
        opacity: 1;
    }
}

.submenu-content:not(.collapsed) {
    animation: slideDown 0.3s ease-out;
}

/* Special styling for different sections */
#accountMenu .settings-link {
    border-left-color: #28a745;
}

#accountMenu .settings-link:hover {
    border-left-color: #28a745;
    color: #28a745;
}

#systemMenu .settings-link {
    border-left-color: #dc3545;
}

#systemMenu .settings-link:hover {
    border-left-color: #dc3545;
    color: #dc3545;
}

#intelligenceMenu .settings-link {
    border-left-color: #17a2b8;
}

#intelligenceMenu .settings-link:hover {
    border-left-color: #17a2b8;
    color: #17a2b8;
}

#managementMenu .settings-link {
    border-left-color: #ffc107;
}

#managementMenu .settings-link:hover {
    border-left-color: #ffc107;
    color: #ffc107;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fonctionnalité de collapse optionnelle pour les sous-menus
    const headings = document.querySelectorAll('.settings-heading');

    headings.forEach(function(heading) {
        heading.addEventListener('click', function() {
            const content = this.nextElementSibling;

            if (content && content.classList.contains('submenu-content')) {
                // Toggle la classe collapsed
                content.classList.toggle('collapsed');
                this.classList.toggle('collapsed');
                
                // Ajouter une animation fluide
                if (!content.classList.contains('collapsed')) {
                    content.style.maxHeight = content.scrollHeight + 'px';
                } else {
                    content.style.maxHeight = '0';
                }
            }
        });
    });
    
    // Initialiser tous les menus comme ouverts
    const allContents = document.querySelectorAll('.submenu-content');
    allContents.forEach(function(content) {
        content.style.maxHeight = content.scrollHeight + 'px';
    });
});
</script>
