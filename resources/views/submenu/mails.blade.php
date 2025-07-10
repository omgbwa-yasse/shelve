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

        /* Style pour les badges de notification */
        .submenu-link .badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
            border-radius: 10px;
            margin-left: auto;
        }

        .submenu-link {
            position: relative;
        }

        /* Style pour les sections de menu */
        .submenu-category-title {
            font-size: 11px;
            text-transform: uppercase;
            color: #5f6368;
            margin: 8px 0 4px 8px;
            letter-spacing: 0.5px;
            font-weight: 500;
        }

        /* Séparateur fin */
        .submenu-divider {
            height: 1px;
            background-color: #e8eaed;
            margin: 8px 0;
        }

        /* Ajout d'icône pour les menus externes */
        .external-badge {
            font-size: 9px;
            background-color: #fbbc04;
            color: #fff;
            padding: 1px 4px;
            border-radius: 3px;
            margin-left: 5px;
            font-weight: 500;
        }
    </style>

    <!-- Courrier Section - Actions principales -->
    <div class="submenu-section">
        <div class="submenu-heading">
            <i class="bi bi-envelope"></i> Actions courantes
        </div>
        <div class="submenu-content" id="actionsCourrierMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-received.create') }}">
                    <i class="bi bi-inbox"></i> {{ __('receive') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-send.create') }}">
                    <i class="bi bi-send"></i> {{ __('send') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mails.inprogress') }}">
                    <i class="bi bi-clock"></i> {{ __('to_receive') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-archive.create') }}">
                    <i class="bi bi-archive"></i> {{ __('archive') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Recherche Section - Consultations -->
    @if(SubmenuPermissions::canAccessSubmenuSection('mails', 'search'))
    <div class="submenu-section">
        <div class="submenu-heading">
            <i class="bi bi-search"></i> Consultations
        </div>
        <div class="submenu-content" id="consultationMenu">
            @can('mail_view')
            <div class="submenu-category-title">Courrier interne</div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-received.index') }}">
                    <i class="bi bi-inbox"></i> {{ __('received_mails') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-send.index') }}">
                    <i class="bi bi-envelope"></i> {{ __('sent_mails') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('batch.index') }}">
                    <i class="bi bi-bookmark"></i> {{ __('my_paraphers') }}
                </a>
            </div>

            <div class="submenu-divider"></div>
            <div class="submenu-category-title">Courrier externe</div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mails.outgoing.index') }}">
                    <i class="bi bi-box-arrow-right"></i> Courrier sortant
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mails.incoming.index') }}">
                    <i class="bi bi-box-arrow-in-left"></i> Courrier entrant
                </a>
            </div>

            <div class="submenu-divider"></div>
            <div class="submenu-category-title">Archives</div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mails.archived') }}">
                    <i class="bi bi-folder"></i> {{ __('archived_mails') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-container.index') }}">
                    <i class="bi bi-archive"></i> {{ __('archive_boxes') }}
                </a>
            </div>

            <div class="submenu-divider"></div>
            <div class="submenu-category-title">Recherche avancée</div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-select-typologies') }}">
                    <i class="bi bi-tags"></i> {{ __('typologies') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-select-date')}}">
                    <i class="bi bi-calendar"></i> {{ __('dates') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mails.advanced.form') }}">
                    <i class="bi bi-search"></i> Recherche avancée
                </a>
            </div>
            @endcan
        </div>
    </div>
    @endif

    <!-- Courrier externe Section -->
    <div class="submenu-section">
        <div class="submenu-heading">
            <i class="bi bi-globe"></i> Courrier externe
        </div>
        <div class="submenu-content" id="externMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mails.incoming.create') }}">
                    <i class="bi bi-box-arrow-in-down"></i> Recevoir courrier externe
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mails.outgoing.create') }}">
                    <i class="bi bi-box-arrow-up"></i> Envoyer courrier externe
                </a>
            </div>
            @can('module_mails_access')
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('external.contacts.index') }}">
                    <i class="bi bi-person-rolodex"></i> Contacts & Organisations
                </a>
            </div>
            @endcan
        </div>
    </div>

    <!-- Administration Section -->
    <div class="submenu-section add-section">
        <div class="submenu-heading">
            <i class="bi bi-gear"></i> Administration
        </div>
        <div class="submenu-content" id="adminMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('batch.create') }}">
                    <i class="bi bi-bookmark-check"></i> {{ __('parapher') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-container.create') }}">
                    <i class="bi bi-archive"></i> {{ __('box_chrono') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('batch-send.create') }}">
                    <i class="bi bi-arrow-right-square"></i> {{ __('send') }} parapheur
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('batch-received.create') }}">
                    <i class="bi bi-arrow-left-square"></i> {{ __('receive') }} parapheur
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

    // Mise à jour du badge de notifications dans le sous-menu
    function updateSidebarNotificationBadge() {
        fetch('/mails/notifications/unread-count')
            .then(response => response.json())
            .then(data => {
                const count = data.count;
                const sidebarBadge = document.getElementById('sidebar-notification-badge');
                const sidebarCount = document.getElementById('sidebar-notification-count');

                if (count > 0 && sidebarBadge && sidebarCount) {
                    sidebarBadge.style.display = 'inline-block';
                    sidebarCount.textContent = count > 99 ? '99+' : count;
                } else if (sidebarBadge) {
                    sidebarBadge.style.display = 'none';
                }
            })
            .catch(error => console.log('Erreur sidebar notifications:', error));
    }

    // Mettre à jour immédiatement et puis toutes les 30 secondes
    updateSidebarNotificationBadge();
    setInterval(updateSidebarNotificationBadge, 30000);
});
</script>
