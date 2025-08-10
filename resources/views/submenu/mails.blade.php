<div class="submenu-container py-2">
    @php
    use App\Helpers\SubmenuPermissions;
    @endphp

    <!-- Styles partagés via _submenu.scss -->

    <!-- Recherche Section - Consultations -->

    <div class="submenu-section">
        <div class="submenu-heading">
            <i class="bi bi-search"></i> Consultations
        </div>
        <div class="submenu-content" id="consultationMenu">

            <div class="submenu-category-title">Courrier interne</div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-received.index') }}">
                    <i class="bi bi-inbox"></i> Reçus
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-send.index') }}">
                    <i class="bi bi-envelope"></i> Envoyés
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('batch.index') }}">
                    <i class="bi bi-bookmark"></i> Parapheurs
                </a>
            </div>

            <div class="submenu-divider"></div>
            <div class="submenu-category-title">Courrier externe</div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mails.send.external.index') }}">
                    <i class="bi bi-send"></i> Envoyer
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mails.received.external.index') }}">
                    <i class="bi bi-inbox"></i> Recevoir
                </a>
            </div>

            <div class="submenu-divider"></div>
            <div class="submenu-category-title">Archives</div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mails.archived') }}">
                    <i class="bi bi-folder"></i> Courrier
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-container.index') }}">
                    <i class="bi bi-archive"></i> Boîtes
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

        </div>
    </div>


    <!-- Création Section -->
    <div class="submenu-section add-section">
        <div class="submenu-heading">
            <i class="bi bi-plus-circle"></i> Création
        </div>
        <div class="submenu-content" id="creationMenu">
            <div class="submenu-category-title">Courrier interne</div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-received.create') }}">
                    <i class="bi bi-plus-square"></i> Reçu
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-send.create') }}">
                    <i class="bi bi-plus-square"></i> Envoyé
                </a>
            </div>

            <div class="submenu-divider"></div>
            <div class="submenu-category-title">Courrier externe</div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mails.send.external.create') }}">
                    <i class="bi bi-plus-square"></i> Sortant
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mails.received.external.create') }}">
                    <i class="bi bi-plus-square"></i> Entrant
                </a>
            </div>

        </div>
    </div>


    <!-- Workflow et Tâches Section -->
    <div class="submenu-section">
        <div class="submenu-heading">
            <i class="bi bi-diagram-3"></i> Workflow et Tâches
        </div>
        <div class="submenu-content" id="workflowTasksMenu">
            <div class="submenu-category-title">Actions rapides</div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('workflows.tasks.create') }}">
                    <i class="bi bi-plus-circle"></i> Créer une tâche
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('workflows.instances.create') }}">
                    <i class="bi bi-plus-square"></i> Créer un workflow
                </a>
            </div>

            <div class="submenu-divider"></div>
            <div class="submenu-category-title">Organisation</div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mails.tasks.index') }}">
                    <i class="bi bi-list-task"></i> Tâches
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mails.workflows.index') }}">
                    <i class="bi bi-diagram-2"></i> Workflows
                </a>
            </div>

            <div class="submenu-divider"></div>
            <div class="submenu-category-title">Personnel</div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mails.tasks.my-tasks') }}">
                    <i class="bi bi-person-check"></i> Mes Tâches
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mails.workflows.my-workflows') }}">
                    <i class="bi bi-person-lines-fill"></i> Mes Workflows
                </a>
            </div>
        </div>
    </div>



    <div class="submenu-section">
        <div class="submenu-heading">
            <i class="bi bi-gear"></i> Administration
        </div>
        <div class="submenu-content" id="adminMenu">

            <div class="submenu-category-title">Parapheurs</div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('batch.create') }}">
                    <i class="bi bi-bookmark-check"></i> {{ __('parapher') }}
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

            <div class="submenu-divider"></div>
            <div class="submenu-category-title">Contenants</div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-container.create') }}">
                    <i class="bi bi-archive"></i> {{ __('box_chrono') }}
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
