<div class="submenu-container py-2">
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" hre        <div class="submenu-heading">
            <i class="bi bi-envelope"></i> {{ __('Communications') }}
        </div>
        <div class="submenu-content" id="communicationsMenu">ttps://fonts.googleapis.com">
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
            display: block; /* Toujours visible par défaut */
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

    <!-- Communications Section -->
    <div class="submenu-section">
        <div class="submenu-heading">
            <i class="bi bi-chat-dots"></i> {{ __('communications') }}
        </div>
        <div class="submenu-content" id="communicationsMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('transactions.index')}}">
                    <i class="bi bi-inbox"></i> {{ __('view_all') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('communications-sort')}}?categ=return-effective">
                    <i class="bi bi-check-circle"></i> {{ __('returned') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('communications-sort')}}?categ=unreturn">
                    <i class="bi bi-dash-circle"></i> {{ __('without_return') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('communications-sort')}}?categ=not-return">
                    <i class="bi bi-x-circle"></i> {{ __('not_returned') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('communications.advanced.form') }}">
                    <i class="bi bi-search"></i> {{ __('advanced') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Reservations Section -->
    <div class="submenu-section">
        <div class="submenu-heading">
            <i class="bi bi-calendar-check"></i> {{ __('reservations') }}
        </div>
        <div class="submenu-content" id="reservationsMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('reservations.index')}}">
                    <i class="bi bi-list-ul"></i> {{ __('view_all') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('reservations-sort', ['categ' => 'InProgress'])}}">
                    <i class="bi bi-hourglass-split"></i> {{ __('under_review') }}
                </a>
            </div>
            {{-- <div class="submenu-item">
                <a class="submenu-link" href="{{ route('reservations-sort', ['categ' => 'approved'])}}">
                    <i class="bi bi-check2-all"></i> {{ __('approved') }}
                </a>
            </div> --}}
        </div>
    </div>

    <!-- Add Section -->
    <div class="submenu-section add-section">
        <div class="submenu-heading">
            <i class="bi bi-plus-circle"></i> {{ __('add') }}
        </div>
        <div class="submenu-content" id="addMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('transactions.create')}}">
                    <i class="bi bi-chat-plus"></i> {{ __('add_communication') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('reservations.create')}}">
                    <i class="bi bi-calendar-plus"></i> {{ __('add_reservation') }}
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
