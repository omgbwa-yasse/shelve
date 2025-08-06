@php
    use Illuminate\Support\Facades\Gate;
@endphp

<div class="communications-submenu" id="communicationsMenu">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        .communications-submenu {
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
        }

        .communications-submenu .submenu-heading {
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
            user-select: none;
        }

        .communications-submenu .submenu-heading:hover {
            background-color: #3367d6;
        }

        .communications-submenu .submenu-heading i {
            margin-right: 8px;
            font-size: 14px;
        }

        .communications-submenu .submenu-section-content {
            padding: 0 0 8px 12px;
            margin-bottom: 8px;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .communications-submenu .submenu-item {
            margin-bottom: 2px;
        }

        .communications-submenu .submenu-link {
            display: flex;
            align-items: center;
            padding: 4px 8px;
            color: #202124;
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.2s ease;
            font-size: 12.5px;
        }

        .communications-submenu .submenu-link:hover {
            background-color: #f1f3f4;
            color: #4285f4;
            text-decoration: none;
        }

        .communications-submenu .submenu-link i {
            margin-right: 8px;
            color: #5f6368;
            font-size: 13px;
        }

        .communications-submenu .submenu-link:hover i {
            color: #4285f4;
        }

        .communications-submenu .add-section .submenu-heading {
            background-color: #34a853;
        }

        .communications-submenu .add-section .submenu-heading:hover {
            background-color: #188038;
        }



        /* Animation de collapse plus fluide */
        .communications-submenu .submenu-section-content.collapsed {
            max-height: 0 !important;
            padding-top: 0;
            padding-bottom: 0;
            margin-bottom: 0;
            opacity: 0;
        }

        .communications-submenu .submenu-heading::after {
            content: '▼';
            margin-left: auto;
            font-size: 12px;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .communications-submenu .submenu-heading.collapsed::after {
            transform: rotate(-90deg);
        }

        /* État par défaut - tout ouvert */
        .communications-submenu .submenu-section-content {
            max-height: 300px;
            opacity: 1;
        }
    </style>

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

    <!-- Add Section -->
    @if(Gate::allows('communications_create') || Gate::allows('reservations_create'))
    <div class="submenu-section add-section">
        <div class="submenu-heading">
            <i class="bi bi-plus-circle"></i> {{ __('create') }}
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
