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
        
        .submenu-content { padding: 0 0 8px 12px; margin-bottom: 8px; display: block; /* Toujours visible par défaut */ }
        
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

    <!-- Recherche Section -->
    <div class="submenu-section">
        <div class="submenu-heading" >
            <i class="bi bi-search"></i> {{ __('search') }}
        </div>
        <div class="submenu-content" id="rechercheMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.index') }}">
                    <i class="bi bi-list-check"></i> {{ __('my_archives') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('record-author.index') }}">
                    <i class="bi bi-person"></i> {{ __('holders') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('record-select-date')}}">
                    <i class="bi bi-calendar"></i> {{ __('dates') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('record-select-word')}}">
                    <i class="bi bi-key"></i> {{ __('keywords') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('record-select-activity')}}">
                    <i class="bi bi-briefcase"></i> {{ __('activities') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('record-select-building')}}">
                    <i class="bi bi-building"></i> {{ __('premises') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('record-select-last')}}">
                    <i class="bi bi-clock-history"></i> {{ __('recent') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.advanced.form')}}">
                    <i class="bi bi-search"></i> {{ __('advanced') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Enregistrement Section -->
    <div class="submenu-section add-section">
        <div class="submenu-heading" >
            <i class="bi bi-journal-plus"></i> {{ __('registration') }}
        </div>
        <div class="submenu-content" id="enregistrementMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.create') }}">
                    <i class="bi bi-plus-square"></i> {{ __('new') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('record-author.create') }}">
                    <i class="bi bi-plus-square"></i> {{ __('producer') }}
                </a>
            </div>
        </div>
    </div>

    <!-- lifeCycle Section -->
    <div class="submenu-section">
        <div class="submenu-heading" >
            <i class="bi bi-cart"></i> {{ __('life_cycle') }}
        </div>
        <div class="submenu-content" id="lifeCycleMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.tostore')}}">
                    <i class="bi bi-folder-check"></i> {{ __('to_transfer') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.toretain')}}">
                    <i class="bi bi-folder-check"></i> {{ __('active_files') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.totransfer')}}">
                    <i class="bi bi-arrow-right-square"></i> {{ __('to_deposit') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.toeliminate')}}">
                    <i class="bi bi-trash"></i> {{ __('to_eliminate') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.tokeep')}}">
                    <i class="bi bi-archive"></i> {{ __('to_keep') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.tosort')}}">
                    <i class="bi bi-sort-down"></i> {{ __('to_sort') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Import / Export Section -->
    <div class="submenu-section">
        <div class="submenu-heading" >
            <i class="bi bi-arrow-down-up"></i> {{ __('import_export') }} (EAD, Excel, SEDA)
        </div>
        <div class="submenu-content" id="importExportMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.import.form') }}">
                    <i class="bi bi-download"></i> {{ __('import') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.export.form') }}">
                    <i class="bi bi-upload"></i> {{ __('export') }}
                </a>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // FonctionnalitÃ© de collapse optionnelle pour les sous-menus
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
