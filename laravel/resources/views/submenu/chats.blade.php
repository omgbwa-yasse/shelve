<div class="submenu-container py-2">
    <!-- Styles partagés via _submenu.scss -->

    <!-- Section Chats -->
    <div class="submenu-section">
        <div class="submenu-heading">
            <i class="bi bi-chat-left-text"></i> {{ __('chats') }}
        </div>
        <div class="submenu-content" id="chatsMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('chats.index') }}">
                    <i class="bi bi-chat-dots"></i> Tous les chats
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('chats.index') }}#privates">
                    <i class="bi bi-envelope"></i> Messages privés
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('workplaces.index') }}">
                    <i class="bi bi-briefcase"></i> Chats des workplaces
                </a>
            </div>
        </div>
    </div>

    <!-- Section Nouveau -->
    <div class="submenu-section add-section">
        <div class="submenu-heading">
            <i class="bi bi-plus-circle"></i> {{ __('creation') }}
        </div>
        <div class="submenu-content" id="chatsCreateMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('chats.index') }}?new=1">
                    <i class="bi bi-envelope-plus"></i> Nouveau message privé
                </a>
            </div>
        </div>
    </div>
</div>
