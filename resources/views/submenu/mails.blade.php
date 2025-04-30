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

    <!-- Recherche Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-toggle="collapse" href="#rechercheMenu" aria-expanded="true" aria-controls="rechercheMenu">
            <i class="bi bi-search"></i> {{ __('search') }}
        </div>
        <div class="collapse show submenu-content" id="rechercheMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-received.index') }}">
                    <i class="bi bi-inbox"></i> {{ __('received_mails') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-send.index') }}">
                    <i class="bi bi-envelope"></i> {{ __('sent_mails') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mails.archived') }}">
                    <i class="bi bi-inbox"></i> {{ __('archived_mails') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-select-typologies') }}">
                    <i class="bi bi-tags"></i> {{ __('typologies') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-select-date')}}">
                    <i class="bi bi-calendar"></i> {{ __('dates') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-container.index') }}">
                    <i class="bi bi-archive"></i> {{ __('archive_boxes') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('batch.index') }}">
                    <i class="bi bi-inbox"></i> {{ __('my_paraphers') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mails.advanced.form') }}">
                    <i class="bi bi-inbox"></i> Advanced
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-outgoing.index') }}">
                    <i class="bi bi-box-arrow-right"></i> Externe sortant
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-incoming.index') }}">
                    <i class="bi bi-box-arrow-in-left"></i> Externe entrant
                </a>
            </div>
        </div>
    </div>

    <!-- Courrier Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-toggle="collapse" href="#courrierMenu" aria-expanded="true" aria-controls="courrierMenu">
            <i class="bi bi-envelope"></i> {{ __('mail') }}
        </div>
        <div class="collapse show submenu-content" id="courrierMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mails.inprogress') }}">
                    <i class="bi bi-clock"></i> {{ __('to_receive') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-received.create') }}">
                    <i class="bi bi-inbox"></i> {{ __('receive') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-send.create') }}">
                    <i class="bi bi-send"></i> {{ __('send') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-archive.create') }}">
                    <i class="bi bi-archive"></i> {{ __('archive') }}
                </a>
            </div>


            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-incoming.create') }}">
                    <i class="bi bi-box-arrow-in-down"></i> Recevoir externe
                </a>
            </div>

            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-outgoing.create') }}">
                    <i class="bi bi-box-arrow-up"></i> Envoyer externe
                </a>
            </div>

        </div>
    </div>

    <!-- Créer Section -->
    <div class="submenu-section add-section">
        <div class="submenu-heading" data-toggle="collapse" href="#creerMenu" aria-expanded="true" aria-controls="creerMenu">
            <i class="bi bi-plus-circle"></i> {{ __('create') }}
        </div>
        <div class="collapse show submenu-content" id="creerMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('batch.create') }}">
                    <i class="bi bi-bookmark-check"></i> {{ __('parapher') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('mail-container.create') }}">
                    <i class="bi bi-archive"></i> {{ __('box_chrono') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Parapheur Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-toggle="collapse" href="#parapheurMenu" aria-expanded="true" aria-controls="parapheurMenu">
            <i class="bi bi-journal-check"></i> {{ __('parapher') }}
        </div>
        <div class="collapse show submenu-content" id="parapheurMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('batch-send.create') }}">
                    <i class="bi bi-inbox"></i> {{ __('send') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('batch-received.create') }}">
                    <i class="bi bi-inbox"></i> {{ __('receive') }}
                </a>
            </div>
        </div>
    </div>
</div>
