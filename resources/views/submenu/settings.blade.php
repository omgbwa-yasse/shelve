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
    </style>

    <!-- Mon Compte Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-toggle="collapse" href="#accountMenu" aria-expanded="true" aria-controls="accountMenu">
            <i class="bi bi-person-circle"></i> {{ __('my_account') }}
        </div>
        <div class="collapse show submenu-content" id="accountMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('users.show', auth()->user()->id) }}">
                    <i class="bi bi-gear"></i> {{ __('my_account') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Autorisations et postes Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-toggle="collapse" href="#authorizationsMenu" aria-expanded="true" aria-controls="authorizationsMenu">
            <i class="bi bi-people"></i> {{ __('authorizations_and_positions') }}
        </div>
        <div class="collapse show submenu-content" id="authorizationsMenu">
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
        <div class="submenu-heading" data-toggle="collapse" href="#rightsMenu" aria-expanded="true" aria-controls="rightsMenu">
            <i class="bi bi-shield-lock"></i> {{ __('rights_and_permissions') }}
        </div>
        <div class="collapse show submenu-content" id="rightsMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('roles.index') }}">
                    <i class="bi bi-person-badge"></i> {{ __('roles') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('role_permissions.index') }}">
                    <i class="bi bi-key"></i> {{ __('assign_permissions') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Tâches Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-toggle="collapse" href="#tasksMenu" aria-expanded="true" aria-controls="tasksMenu">
            <i class="bi bi-list-task"></i> {{ __('tasks') }}
        </div>
        <div class="collapse show submenu-content" id="tasksMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('taskstatus.index') }}">
                    <i class="bi bi-tag"></i> {{ __('task_types') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('tasktype.index') }}">
                    <i class="bi bi-flag"></i> {{ __('task_statuses') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Courrier Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-toggle="collapse" href="#mailMenu" aria-expanded="true" aria-controls="mailMenu">
            <i class="bi bi-envelope"></i> {{ __('mail') }}
        </div>
        <div class="collapse show submenu-content" id="mailMenu">
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
                    <i class="bi bi-flag"></i> {{ __('actions') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Répertoire Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-toggle="collapse" href="#directoryMenu" aria-expanded="true" aria-controls="directoryMenu">
            <i class="bi bi-file-text"></i> {{ __('directory') }}
        </div>
        <div class="collapse show submenu-content" id="directoryMenu">
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
        <div class="submenu-heading" data-toggle="collapse" href="#transferMenu" aria-expanded="true" aria-controls="transferMenu">
            <i class="bi bi-arrow-right-circle"></i> {{ __('transfer') }}
        </div>
        <div class="collapse show submenu-content" id="transferMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('transferring-status.index') }}">
                    <i class="bi bi-flag"></i> {{ __('statuses') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Communication Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-toggle="collapse" href="#communicationMenu" aria-expanded="true" aria-controls="communicationMenu">
            <i class="bi bi-chat-dots"></i> {{ __('communication') }}
        </div>
        <div class="collapse show submenu-content" id="communicationMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('communication-status.index') }}">
                    <i class="bi bi-flag"></i> {{ __('communication_status') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('reservation-status.index') }}">
                    <i class="bi bi-flag"></i> {{ __('reservation_status') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Dépôt Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-toggle="collapse" href="#depositMenu" aria-expanded="true" aria-controls="depositMenu">
            <i class="bi bi-building"></i> {{ __('deposit') }}
        </div>
        <div class="collapse show submenu-content" id="depositMenu">
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
        <div class="submenu-heading" data-toggle="collapse" href="#managementMenu" aria-expanded="true" aria-controls="managementMenu">
            <i class="bi bi-gear"></i> {{ __('management_tools') }}
        </div>
        <div class="collapse show submenu-content" id="managementMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('sorts.index') }}">
                    <i class="bi bi-sort-alpha-down"></i> {{ __('retention_final_sorts') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('languages.index') }}">
                    <i class="bi bi-translate"></i> {{ __('thesaurus_languages') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('term-categories.index') }}">
                    <i class="bi bi-bookmark"></i> {{ __('thesaurus_categories') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('term-equivalent-types.index') }}">
                    <i class="bi bi-arrows-angle-expand"></i> {{ __('thesaurus_equivalents') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('term-types.index') }}">
                    <i class="bi bi-type"></i> {{ __('thesaurus_types') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Système Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-toggle="collapse" href="#systemMenu" aria-expanded="true" aria-controls="systemMenu">
            <i class="bi bi-cpu"></i> {{ __('system') }}
        </div>
        <div class="collapse show submenu-content" id="systemMenu">
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
