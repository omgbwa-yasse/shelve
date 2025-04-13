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
        
        .submenu-content {
            padding: 0 0 8px 12px;
            margin-bottom: 8px;
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
    </style>

    <!-- Plan de classement Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-toggle="collapse" href="#planClassementMenu" aria-expanded="true" aria-controls="planClassementMenu">
            <i class="bi bi-grid"></i> {{ __('classification_plan') }}
        </div>
        <div class="collapse show submenu-content" id="planClassementMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('activities.index') }}">
                    <i class="bi bi-list-check"></i> {{ __('all_classes') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('activities.create') }}">
                    <i class="bi bi-plus-square"></i> {{ __('add_class') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Référentiel de conservation Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-toggle="collapse" href="#referentielConservationMenu" aria-expanded="true" aria-controls="referentielConservationMenu">
            <i class="bi bi-archive"></i> {{ __('retention_schedule') }}
        </div>
        <div class="collapse show submenu-content" id="referentielConservationMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('retentions.index') }}">
                    <i class="bi bi-clock-history"></i> {{ __('all_durations') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('retentions.create') }}">
                    <i class="bi bi-plus-square"></i> {{ __('add_rule') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Communicabilité Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-toggle="collapse" href="#communicabiliteMenu" aria-expanded="true" aria-controls="communicabiliteMenu">
            <i class="bi bi-chat-square-text"></i> {{ __('communicability') }}
        </div>
        <div class="collapse show submenu-content" id="communicabiliteMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('communicabilities.index')}}">
                    <i class="bi bi-list-check"></i> {{ __('all_classes') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('communicabilities.create')}}">
                    <i class="bi bi-plus-square"></i> {{ __('add_class') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Organigramme Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-toggle="collapse" href="#organigrammeMenu" aria-expanded="true" aria-controls="organigrammeMenu">
            <i class="bi bi-diagram-3"></i> {{ __('organization_chart') }}
        </div>
        <div class="collapse show submenu-content" id="organigrammeMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('organisations.index')}}">
                    <i class="bi bi-building"></i> {{ __('all_units') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('organisations.create')}}">
                    <i class="bi bi-plus-square"></i> {{ __('add_organization') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Thésaurus Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-toggle="collapse" href="#thesaurusMenu" aria-expanded="true" aria-controls="thesaurusMenu">
            <i class="bi bi-book-half"></i> {{ __('thesaurus') }}
        </div>
        <div class="collapse show submenu-content" id="thesaurusMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('terms.index') }}">
                    <i class="bi bi-tree"></i> {{ __('view_branches') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('terms.create') }}">
                    <i class="bi bi-plus-square"></i> {{ __('add_word') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Boite à outils Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-toggle="collapse" href="#outilsMenu" aria-expanded="true" aria-controls="outilsMenu">
            <i class="bi bi-tools"></i> {{ __('toolbox') }}
        </div>
        <div class="collapse show submenu-content" id="outilsMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('barcode.create') }}">
                    <i class="bi bi-upc-scan"></i> {{ __('barcode') }}
                </a>
            </div>
        </div>
    </div>
</div>
