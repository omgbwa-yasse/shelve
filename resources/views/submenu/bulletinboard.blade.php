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
        <div class="submenu-heading" data-toggle="collapse" href="#bulletinboardsearch" aria-expanded="true" aria-controls="bulletinboardsearch">
            <i class="bi bi-search"></i> Recherche
        </div>
        <div class="collapse show submenu-content" id="bulletinboardsearch">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('bulletin-boards.index') }}">
                    <i class="bi bi-grid"></i> Babillards
                </a>
            </div>
        </div>
    </div>

    <!-- Création Section -->
    <div class="submenu-section add-section">
        <div class="submenu-heading" data-toggle="collapse" href="#bulletinboardcreate" aria-expanded="true" aria-controls="bulletinboardcreate">
            <i class="bi bi-plus-circle"></i> Création
        </div>
        <div class="collapse show submenu-content" id="bulletinboardcreate">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('bulletin-boards.create') }}">
                    <i class="bi bi-pin-angle"></i> Nouveau babillard
                </a>
            </div>
        </div>
    </div>

    <!-- Administration Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-toggle="collapse" href="#bulletinboardadmin" aria-expanded="true" aria-controls="bulletinboardadmin">
            <i class="bi bi-gear"></i> Administration
        </div>
        <div class="collapse show submenu-content" id="bulletinboardadmin">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('bulletin-boards.admin.index') }}">
                    <i class="bi bi-speedometer2"></i> Tableau de bord
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('bulletin-boards.admin.settings') }}">
                    <i class="bi bi-sliders"></i> Paramètres
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('bulletin-boards.admin.users') }}">
                    <i class="bi bi-people"></i> Utilisateurs
                </a>
            </div>
        </div>
    </div>
</div>
