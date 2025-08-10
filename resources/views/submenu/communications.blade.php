@php
    use Illuminate\Support\Facades\Gate;
@endphp

<div class="communications-submenu" id="communicationsMenu">
    <!-- Styles partagés via _submenu.scss -->

    <!-- Communications Section -->
    @if(Gate::allows('communications_view'))
    <div class="submenu-section">
        <div class="submenu-heading">
            <i class="bi bi-chat-dots"></i> {{ __('communications') }}
        </div>
        <div class="submenu-section-content" id="communicationsSection">
            @can('communications_view')
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('communications.index')}}">
                    <i class="bi bi-inbox"></i> {{ __('view_all_communications') }}
                </a>
            </div>
            @endcan
        </div>
    </div>
    @endif

    <!-- Reservations Section -->
    @if(Gate::allows('reservations_view'))
    <div class="submenu-section">
        <div class="submenu-heading">
            <i class="bi bi-calendar-check"></i> {{ __('reservations') }}
        </div>
        <div class="submenu-section-content" id="reservationsSection">
            @can('reservations_view')
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('communications.reservations.index')}}">
                    <i class="bi bi-list-ul"></i> {{ __('view_all_reservations') }}
                </a>
            </div>
            @endcan
        </div>
    </div>
    @endif

    <!-- Création Section -->
    @if(Gate::allows('communications_create') || Gate::allows('reservations_create'))
    <div class="submenu-section add-section">
        <div class="submenu-heading">
            <i class="bi bi-plus-circle"></i> Création
        </div>
        <div class="submenu-section-content" id="addSection">
            @can('communications_create')
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('communications.transactions.create')}}">
                    <i class="bi bi-plus-circle"></i> {{ __('new_communication') }}
                </a>
            </div>
            @endcan
            @can('reservations_create')
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('communications.reservations.create')}}">
                    <i class="bi bi-calendar-plus"></i> {{ __('new_reservation') }}
                </a>
            </div>
            @endcan
        </div>
    </div>
    @endif

    <!-- Les sections Configuration et Tools ont été retirées intentionnellement -->
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Communications submenu initialized');

    // Sélectionner uniquement les éléments de notre sous-menu
    const menuContainer = document.getElementById('communicationsMenu');
    if (!menuContainer) {
        console.error('Communications menu container not found');
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
