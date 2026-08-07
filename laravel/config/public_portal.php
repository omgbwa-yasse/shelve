<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Portail Public - Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration générale du portail public pour les utilisateurs externes
    |
    */

    'registration' => [
        /*
        |--------------------------------------------------------------------------
        | Approbation automatique
        |--------------------------------------------------------------------------
        |
        | Détermine si les nouveaux utilisateurs sont automatiquement approuvés
        | ou s'ils nécessitent une validation manuelle par un administrateur
        |
        */
        'auto_approve' => env('PUBLIC_PORTAL_AUTO_APPROVE', false),

        /*
        |--------------------------------------------------------------------------
        | Vérification email requise
        |--------------------------------------------------------------------------
        |
        | Détermine si la vérification de l'email est obligatoire avant
        | d'utiliser les fonctionnalités du portail
        |
        */
        'requires_verification' => env('PUBLIC_PORTAL_REQUIRES_VERIFICATION', true),

        /*
        |--------------------------------------------------------------------------
        | Domaines email autorisés
        |--------------------------------------------------------------------------
        |
        | Liste des domaines email autorisés pour l'inscription.
        | Laisser vide pour autoriser tous les domaines
        |
        */
        'allowed_domains' => env('PUBLIC_PORTAL_ALLOWED_DOMAINS', ''),
    ],

    'documents' => [
        /*
        |--------------------------------------------------------------------------
        | Limites des demandes de documents
        |--------------------------------------------------------------------------
        |
        | Configuration des limites pour les demandes de documents
        |
        */
        'max_requests_per_day' => env('PUBLIC_PORTAL_MAX_REQUESTS_PER_DAY', 10),
        'max_requests_per_month' => env('PUBLIC_PORTAL_MAX_REQUESTS_PER_MONTH', 50),

        /*
        |--------------------------------------------------------------------------
        | Types de fichiers autorisés
        |--------------------------------------------------------------------------
        |
        | Extensions de fichiers autorisées pour les pièces jointes
        |
        */
        'allowed_file_types' => ['pdf', 'jpg', 'jpeg', 'png', 'docx', 'doc', 'txt'],

        /*
        |--------------------------------------------------------------------------
        | Taille maximale des fichiers
        |--------------------------------------------------------------------------
        |
        | Taille maximale en kilooctets pour les fichiers uploadés
        |
        */
        'max_file_size' => env('PUBLIC_PORTAL_MAX_FILE_SIZE', 10240), // 10MB

        /*
        |--------------------------------------------------------------------------
        | Délai d'expiration des liens
        |--------------------------------------------------------------------------
        |
        | Durée en jours avant expiration des liens de téléchargement
        |
        */
        'download_link_expiry_days' => env('PUBLIC_PORTAL_DOWNLOAD_EXPIRY_DAYS', 30),
    ],

    'chat' => [
        /*
        |--------------------------------------------------------------------------
        | Chat activé
        |--------------------------------------------------------------------------
        |
        | Active ou désactive le système de chat du portail public
        |
        */
        'enabled' => env('PUBLIC_PORTAL_CHAT_ENABLED', true),

        /*
        |--------------------------------------------------------------------------
        | Participants maximum
        |--------------------------------------------------------------------------
        |
        | Nombre maximum de participants par conversation de groupe
        |
        */
        'max_participants' => env('PUBLIC_PORTAL_MAX_CHAT_PARTICIPANTS', 50),

        /*
        |--------------------------------------------------------------------------
        | Longueur maximale des messages
        |--------------------------------------------------------------------------
        |
        | Nombre maximum de caractères par message
        |
        */
        'max_message_length' => env('PUBLIC_PORTAL_MAX_MESSAGE_LENGTH', 1000),

        /*
        |--------------------------------------------------------------------------
        | Historique des messages
        |--------------------------------------------------------------------------
        |
        | Nombre de jours de conservation des messages
        |
        */
        'message_retention_days' => env('PUBLIC_PORTAL_MESSAGE_RETENTION_DAYS', 365),
    ],

    'events' => [
        /*
        |--------------------------------------------------------------------------
        | Inscription aux événements
        |--------------------------------------------------------------------------
        |
        | Configuration des inscriptions aux événements
        |
        */
        'registration_deadline_hours' => env('PUBLIC_PORTAL_EVENT_REGISTRATION_DEADLINE', 24),
        'max_registrations_per_user_per_month' => env('PUBLIC_PORTAL_MAX_EVENT_REGISTRATIONS', 10),
        'allow_cancellation_hours_before' => env('PUBLIC_PORTAL_EVENT_CANCELLATION_DEADLINE', 24),
    ],

    'search' => [
        /*
        |--------------------------------------------------------------------------
        | Recherche et logs
        |--------------------------------------------------------------------------
        |
        | Configuration de la recherche et des logs
        |
        */
        'log_searches' => env('PUBLIC_PORTAL_LOG_SEARCHES', true),
        'max_search_results' => env('PUBLIC_PORTAL_MAX_SEARCH_RESULTS', 100),
        'search_results_per_page' => env('PUBLIC_PORTAL_SEARCH_RESULTS_PER_PAGE', 20),
    ],

    'feedback' => [
        /*
        |--------------------------------------------------------------------------
        | Système de feedback
        |--------------------------------------------------------------------------
        |
        | Configuration du système de retours/commentaires
        |
        */
        'max_feedback_per_day' => env('PUBLIC_PORTAL_MAX_FEEDBACK_PER_DAY', 5),
        'require_rating' => env('PUBLIC_PORTAL_REQUIRE_RATING', false),
        'auto_respond' => env('PUBLIC_PORTAL_AUTO_RESPOND_FEEDBACK', true),
    ],

    'security' => [
        /*
        |--------------------------------------------------------------------------
        | Sécurité
        |--------------------------------------------------------------------------
        |
        | Paramètres de sécurité du portail public
        |
        */
        'rate_limit_per_minute' => env('PUBLIC_PORTAL_RATE_LIMIT', 60),
        'session_lifetime' => env('PUBLIC_PORTAL_SESSION_LIFETIME', 120), // minutes
        'password_min_length' => env('PUBLIC_PORTAL_PASSWORD_MIN_LENGTH', 8),
    ],

    'notifications' => [
        /*
        |--------------------------------------------------------------------------
        | Notifications
        |--------------------------------------------------------------------------
        |
        | Configuration des notifications email
        |
        */
        'admin_email' => env('PUBLIC_PORTAL_ADMIN_EMAIL', 'admin@example.com'),
        'from_email' => env('PUBLIC_PORTAL_FROM_EMAIL', 'noreply@example.com'),
        'from_name' => env('PUBLIC_PORTAL_FROM_NAME', 'Portail Public'),
    ],
];
