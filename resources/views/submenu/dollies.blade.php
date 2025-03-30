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

    <!-- Search Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-toggle="collapse" href="#searchMenu" aria-expanded="true" aria-controls="searchMenu">
            <i class="bi bi-search"></i> {{ __('search') }}
        </div>
        <div class="collapse show submenu-content" id="searchMenu">
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
        <div class="submenu-heading" data-toggle="collapse" href="#createMenu" aria-expanded="true" aria-controls="createMenu">
            <i class="bi bi-plus-circle"></i> {{ __('create') }}
        </div>
        <div class="collapse show submenu-content" id="createMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('dolly.create') }}">
                    <i class="bi bi-cart3"></i> {{ __('cart') }}
                </a>
            </div>
        </div>
    </div>
</div>
