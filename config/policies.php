<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Policies Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file allows you to customize the behavior of
    | your application's authorization policies.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configure caching behavior for organization access checks.
    |
    */
    'cache' => [
        'enabled' => env('POLICIES_CACHE_ENABLED', true),
        'ttl' => env('POLICIES_CACHE_TTL', 600), // 10 minutes in seconds
        'prefix' => env('POLICIES_CACHE_PREFIX', 'policy_'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Messages
    |--------------------------------------------------------------------------
    |
    | Default error messages for authorization failures.
    | These can be overridden in individual policies.
    |
    */
    'messages' => [
        'no_permission' => 'Vous n\'avez pas la permission d\'effectuer cette action.',
        'no_organisation' => 'Vous devez appartenir à une organisation pour effectuer cette action.',
        'wrong_organisation' => 'Vous n\'avez pas accès à cette ressource dans votre organisation actuelle.',
        'not_authenticated' => 'Vous devez être connecté pour effectuer cette action.',
        'account_not_approved' => 'Votre compte doit être approuvé pour effectuer cette action.',
        'email_not_verified' => 'Vous devez vérifier votre email pour effectuer cette action.',
        'resource_not_found' => 'La ressource demandée n\'existe pas ou vous n\'y avez pas accès.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Super Admin Role
    |--------------------------------------------------------------------------
    |
    | Define the role name that grants all permissions automatically.
    |
    */
    'super_admin_role' => env('SUPER_ADMIN_ROLE', 'super-admin'),

    /*
    |--------------------------------------------------------------------------
    | Organisation Access Methods
    |--------------------------------------------------------------------------
    |
    | Define the methods used to check organisation access for models.
    | These are checked in order until one succeeds.
    |
    */
    'organisation_access_methods' => [
        'direct_organisations',     // $model->organisations relationship
        'organisation_id_column',   // $model->organisation_id property
        'through_activity',         // $model->activity->organisations
        'through_user',             // $model->user->organisations
        'through_building',         // $model->building->organisations
    ],

    /*
    |--------------------------------------------------------------------------
    | Public Policies Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for public-facing policies.
    |
    */
    'public' => [
        'require_approved' => env('PUBLIC_REQUIRE_APPROVED', true),
        'require_verified_email' => env('PUBLIC_REQUIRE_VERIFIED_EMAIL', true),
        'registration_cancellation_hours' => env('PUBLIC_CANCELLATION_HOURS', 24),
    ],

    /*
    |--------------------------------------------------------------------------
    | Advanced Features
    |--------------------------------------------------------------------------
    |
    | Enable or disable advanced policy features.
    |
    */
    'features' => [
        'daily_creation_limits' => env('POLICIES_DAILY_LIMITS', true),
        'processing_locks' => env('POLICIES_PROCESSING_LOCKS', true),
        'confidentiality_checks' => env('POLICIES_CONFIDENTIALITY', true),
        'quarantine_checks' => env('POLICIES_QUARANTINE', true),
        'legal_holds' => env('POLICIES_LEGAL_HOLDS', true),
        'retention_periods' => env('POLICIES_RETENTION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Configure logging for authorization events.
    |
    */
    'logging' => [
        'enabled' => env('POLICIES_LOGGING_ENABLED', false),
        'channel' => env('POLICIES_LOG_CHANNEL', 'daily'),
        'log_denied_access' => env('POLICIES_LOG_DENIED', true),
        'log_granted_access' => env('POLICIES_LOG_GRANTED', false),
        'log_super_admin_access' => env('POLICIES_LOG_SUPER_ADMIN', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    |
    | Track policy performance and usage statistics.
    |
    */
    'monitoring' => [
        'enabled' => env('POLICIES_MONITORING_ENABLED', false),
        'slow_query_threshold' => env('POLICIES_SLOW_THRESHOLD', 100), // milliseconds
        'track_usage_stats' => env('POLICIES_TRACK_STATS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Rules for validating policy structure and naming conventions.
    |
    */
    'validation' => [
        'enforce_base_policy' => env('POLICIES_ENFORCE_BASE', true),
        'require_response_types' => env('POLICIES_REQUIRE_RESPONSE_TYPES', true),
        'permission_naming_pattern' => '/^[a-z_]+_[a-z_]+$/',
        'allowed_return_types' => ['bool', 'bool|Response', 'Response'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Settings
    |--------------------------------------------------------------------------
    |
    | Settings for the policy migration command.
    |
    */
    'migration' => [
        'backup_extension' => '.backup',
        'skip_patterns' => [
            'BasePolicy.php',
            'PublicBasePolicy.php',
            'AdvancedRecordPolicy.php',
        ],
        'force_migration' => env('POLICIES_FORCE_MIGRATION', false),
    ],

];
