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

    <!-- Communications Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-toggle="collapse" href="#communicationsMenu" aria-expanded="true" aria-controls="communicationsMenu">
            <i class="bi bi-chat-dots"></i> {{ __('communications') }}
        </div>
        <div class="collapse show submenu-content" id="communicationsMenu">
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
        <div class="submenu-heading" data-toggle="collapse" href="#reservationsMenu" aria-expanded="true" aria-controls="reservationsMenu">
            <i class="bi bi-calendar-check"></i> {{ __('reservations') }}
        </div>
        <div class="collapse show submenu-content" id="reservationsMenu">
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
        <div class="submenu-heading" data-toggle="collapse" href="#addMenu" aria-expanded="true" aria-controls="addMenu">
            <i class="bi bi-plus-circle"></i> {{ __('add') }}
        </div>
        <div class="collapse show submenu-content" id="addMenu">
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