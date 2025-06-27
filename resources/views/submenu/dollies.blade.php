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

    <!-- Search Section -->
    <div class="submenu-section">
        <div class="submenu-heading" >
            <i class="bi bi-search"></i> {{ __('search') }}
        </div>
        <div class="submenu-content" id="searchMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('dolly.index') }}">
                    <i class="bi bi-cart3"></i> {{ __('all_carts') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('dollies-sort')}}?categ=mail">
                    <i class="bi bi-cart3"></i> {{ __('mail') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('dollies-sort')}}?categ=record">
                    <i class="bi bi-cart3"></i> {{ __('archives') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('dollies-sort')}}?categ=communication">
                    <i class="bi bi-cart3"></i> {{ __('communication') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('dollies-sort')}}?categ=room">
                    <i class="bi bi-cart3"></i> {{ __('room') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('dollies-sort')}}?categ=shelf">
                    <i class="bi bi-cart3"></i> {{ __('shelf') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('dollies-sort')}}?categ=container">
                    <i class="bi bi-cart3"></i> {{ __('archive_boxes') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('dollies-sort')}}?categ=slip_record">
                    <i class="bi bi-cart3"></i> {{ __('archives_transfer') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('dollies-sort')}}?categ=slip">
                    <i class="bi bi-cart3"></i> {{ __('transfer') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Create Section -->
    <div class="submenu-section add-section">
        <div class="submenu-heading" >
            <i class="bi bi-plus-circle"></i> {{ __('create') }}
        </div>
        <div class="submenu-content" id="createMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('dolly.create') }}">
                    <i class="bi bi-cart3"></i> {{ __('cart') }}
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
