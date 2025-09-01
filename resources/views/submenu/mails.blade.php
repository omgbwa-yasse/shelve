<div class="submenu-container py-2">
    @php
    use App\Helpers\SubmenuPermissions;
    @endphp

    <!-- Styles partagés via _submenu.scss -->

    <!-- Recherche Section - Consultations -->

    <div class="submenu-section">
        <div class="submenu-heading">
            <i class="bi bi-search"></i> {{ __('Consultations') }}
        </div>
        <div class="submenu-content" id="consultationMenu">

            <div class="submenu-category-title">{{ __('Courrier interne') }}</div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-received.index') }}">
                    <i class="bi bi-inbox"></i> {{ __('Reçus') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-send.index') }}">
                    <i class="bi bi-envelope"></i> {{ __('Envoyés') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-received.returned') }}">
                    <i class="bi bi-arrow-return-left"></i> {{ __('returned') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-received.toReturn') }}">
                    <i class="bi bi-arrow-return-right"></i> {{ __('to_return') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('batch.index') }}">
                    <i class="bi bi-bookmark"></i> {{ __('Parapheurs') }}
                </a>
            </div>

            <div class="submenu-divider"></div>
            <div class="submenu-category-title">{{ __('Courrier externe') }}</div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mails.send.external.index') }}">
                    <i class="bi bi-send"></i> {{ __('Envoyer') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mails.received.external.index') }}">
                    <i class="bi bi-inbox"></i> {{ __('Recevoir') }}
                </a>
            </div>

            <div class="submenu-divider"></div>
            <div class="submenu-category-title">{{ __('Archives') }}</div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mails.archived') }}">
                    <i class="bi bi-folder"></i> {{ __('Courrier') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-container.index') }}">
                    <i class="bi bi-archive"></i> {{ __('Boîtes') }}
                </a>
            </div>

            <div class="submenu-divider"></div>
            <div class="submenu-category-title">{{ __('Recherche avancée') }}</div>
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
                    <i class="bi bi-search"></i> {{ __('Recherche avancée') }}
                </a>
            </div>

        </div>
    </div>


    <!-- Création Section -->
    <div class="submenu-section add-section">
        <div class="submenu-heading">
            <i class="bi bi-plus-circle"></i> {{ __('Création') }}
        </div>
        <div class="submenu-content" id="creationMenu">
            <div class="submenu-category-title">{{ __('Courrier interne') }}</div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-received.create') }}">
                    <i class="bi bi-plus-square"></i> {{ __('Reçus') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-send.create') }}">
                    <i class="bi bi-plus-square"></i> {{ __('Envoyés') }}
                </a>
            </div>

            <div class="submenu-divider"></div>
            <div class="submenu-category-title">{{ __('Courrier externe') }}</div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mails.send.external.create') }}">
                    <i class="bi bi-plus-square"></i> {{ __('Sortant') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mails.received.external.create') }}">
                    <i class="bi bi-plus-square"></i> {{ __('Entrant') }}
                </a>
            </div>

        </div>
    </div>


    <!-- La section Workflow et Tâches a été supprimée -->


    <div class="submenu-section">
        <div class="submenu-heading">
            <i class="bi bi-gear"></i> {{ __('Administration') }}
        </div>
        <div class="submenu-content" id="adminMenu">

            <div class="submenu-category-title">{{ __('Parapheurs') }}</div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('batch.create') }}">
                    <i class="bi bi-bookmark-check"></i> {{ __('parapher') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('batch-send.create') }}">
                    <i class="bi bi-arrow-right-square"></i> {{ __('send') }} {{ __('parapheur') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('batch-received.create') }}">
                    <i class="bi bi-arrow-left-square"></i> {{ __('receive') }} {{ __('parapheur') }}
                </a>
            </div>

            <div class="submenu-divider"></div>
            <div class="submenu-category-title">{{ __('Contenants') }}</div>
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

    // Notifications retirées
});
</script>
