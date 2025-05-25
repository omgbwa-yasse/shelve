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

        /* Styles spécifiques pour la section AI */
        .ai.section .submenu-heading {
            background-color: #9c27b0;
        }

        .ai.section .submenu-heading:hover {
            background-color: #7b1fa2;
        }

        .ai.config-section .submenu-heading {
            background-color: #673ab7;
        }

        .ai.config-section .submenu-heading:hover {
            background-color: #5e35b1;
        }
    </style>


    <!-- Intelligence Artificielle Section -->
    <div class="submenu-section ai.section">
        <div class="submenu-heading" data-toggle="collapse" href="#aiMenu" aria-expanded="true" aria-controls="aiMenu">
            <i class="bi bi-robot"></i> {{ __('artificial_intelligence') }}
        </div>
        <div class="collapse show submenu-content" id="aiMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('ai.chats.index') }}">
                    <i class="bi bi-chat-dots"></i> {{ __('ai_chats') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('ai.interactions.index') }}">
                    <i class="bi bi-arrow-left-right"></i> {{ __('ai_interactions') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('ai.actions.index') }}">
                    <i class="bi bi-lightning"></i> {{ __('ai_actions') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('ai.action-batches.index') }}">
                    <i class="bi bi-collection"></i> {{ __('ai_action_batches') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('ai.jobs.index') }}">
                    <i class="bi bi-cpu"></i> {{ __('ai_jobs') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('ai.feedback.index') }}">
                    <i class="bi bi-star"></i> {{ __('ai_feedback') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Configuration IA Section -->
    <div class="submenu-section ai.config-section">
        <div class="submenu-heading" data-toggle="collapse" href="#aiConfigMenu" aria-expanded="true" aria-controls="aiConfigMenu">
            <i class="bi bi-sliders"></i> {{ __('ai_configuration') }}
        </div>
        <div class="collapse show submenu-content" id="aiConfigMenu">

            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('ai.models.index') }}">
                    <i class="bi bi-box"></i> {{ __('ai_models') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('ai.action-types.index') }}">
                    <i class="bi bi-tag"></i> {{ __('ai_action_types') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('ai.prompt-templates.index') }}">
                    <i class="bi bi-file-text"></i> {{ __('ai_prompt_templates') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('ai.integrations.index') }}">
                    <i class="bi bi-plug"></i> {{ __('ai_integrations') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('ai.training-data.index') }}">
                    <i class="bi bi-mortarboard"></i> {{ __('ai_training_data') }}
                </a>
            </div>
        </div>
    </div>

</div>
