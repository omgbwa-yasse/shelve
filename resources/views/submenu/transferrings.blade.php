<div class="submenu-container py-3">
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        .submenu-container {
            font-family: 'Inter', sans-serif;
        }
        
        .submenu-heading {
            background-color: #4285f4;
            color: white;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 12px;
            font-weight: 500;
            font-size: 15px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .submenu-heading:hover {
            background-color: #3367d6;
        }
        
        .submenu-heading i {
            margin-right: 10px;
        }
        
        .submenu-content {
            padding: 0 0 16px 16px;
            margin-bottom: 16px;
        }
        
        .submenu-item {
            margin-bottom: 6px;
        }
        
        .submenu-link {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            color: #202124;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.2s ease;
            font-size: 14px;
        }
        
        .submenu-link:hover {
            background-color: #f1f3f4;
            color: #4285f4;
            text-decoration: none;
        }
        
        .submenu-link i {
            margin-right: 10px;
            color: #5f6368;
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

    <!-- Recherche Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-toggle="collapse" href="#rechercheMenu" aria-expanded="true" aria-controls="rechercheMenu">
            <i class="bi bi-search"></i> {{ __('search') }}
        </div>
        <div class="collapse show submenu-content" id="rechercheMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('search.slips.advanced') }}">
                    <i class="bi bi-search"></i> {{ __('advanced') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('slips.index') }}">
                    <i class="bi bi-building"></i> {{ __('my_slips') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('slips-select-date') }}">
                    <i class="bi bi-list"></i> {{ __('dates') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('slips-select-organisation') }}?categ=organisation">
                    <i class="bi bi-list"></i> {{ __('organizations') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Suivi de transfert Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-toggle="collapse" href="#suiviTransfertMenu" aria-expanded="true" aria-controls="suiviTransfertMenu">
            <i class="bi bi-arrow-right-circle"></i> {{ __('transfer_tracking') }}
        </div>
        <div class="collapse show submenu-content" id="suiviTransfertMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('slips-sort') }}?categ=project">
                    <i class="bi bi-folder"></i> {{ __('projects') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('slips-sort') }}?categ=received">
                    <i class="bi bi-envelope-check"></i> {{ __('received') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('slips-sort') }}?categ=approved">
                    <i class="bi bi-check-circle"></i> {{ __('approved') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('slips-sort') }}?categ=integrated">
                    <i class="bi bi-folder-plus"></i> {{ __('integrated') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Création Section -->
    <div class="submenu-section add-section">
        <div class="submenu-heading" data-toggle="collapse" href="#enregistrementMenu" aria-expanded="true" aria-controls="enregistrementMenu">
            <i class="bi bi-plus-circle"></i> {{ __('create') }}
        </div>
        <div class="collapse show submenu-content" id="enregistrementMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('slips.create') }}">
                    <i class="bi bi-building"></i> {{ __('slip') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('slips.containers.index') }}">
                    <i class="bi bi-archive"></i> {{ __('box_chrono') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Import / Export Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-toggle="collapse" href="#importExportMenu" aria-expanded="true" aria-controls="importExportMenu">
            <i class="bi bi-arrow-down-up"></i> {{ __('import_export') }} (EAD, Excel, SEDA)
        </div>
        <div class="collapse show submenu-content" id="importExportMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('slips.import.form') }}">
                    <i class="bi bi-download"></i> {{ __('import') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('slips.export.form') }}">
                    <i class="bi bi-upload"></i> {{ __('export') }}
                </a>
            </div>
        </div>
    </div>
</div>
