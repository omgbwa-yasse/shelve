<?php

namespace App\Helpers;

class PublicPortalHelper
{
    /**
     * Vérifie si l'auto-approbation est activée
     */
    public static function isAutoApprovalEnabled(): bool
    {
        return config('public_portal.registration.auto_approve', false);
    }

    /**
     * Vérifie si la vérification email est requise
     */
    public static function isEmailVerificationRequired(): bool
    {
        return config('public_portal.registration.requires_verification', true);
    }

    /**
     * Retourne le nombre maximum de demandes de documents par jour
     */
    public static function getMaxDocumentRequestsPerDay(): int
    {
        return config('public_portal.documents.max_requests_per_day', 10);
    }

    /**
     * Retourne les types de fichiers autorisés
     */
    public static function getAllowedFileTypes(): array
    {
        return config('public_portal.documents.allowed_file_types', ['pdf', 'jpg', 'png']);
    }

    /**
     * Retourne la taille maximale des fichiers en KB
     */
    public static function getMaxFileSize(): int
    {
        return config('public_portal.documents.max_file_size', 10240);
    }

    /**
     * Vérifie si le chat est activé
     */
    public static function isChatEnabled(): bool
    {
        return config('public_portal.chat.enabled', true);
    }

    /**
     * Retourne le nombre maximum de participants par chat
     */
    public static function getMaxChatParticipants(): int
    {
        return config('public_portal.chat.max_participants', 50);
    }

    /**
     * Retourne le délai limite pour l'inscription aux événements (en heures)
     */
    public static function getEventRegistrationDeadlineHours(): int
    {
        return config('public_portal.events.registration_deadline_hours', 24);
    }

    /**
     * Retourne le délai limite pour l'annulation d'inscription (en heures)
     */
    public static function getEventCancellationDeadlineHours(): int
    {
        return config('public_portal.events.allow_cancellation_hours_before', 24);
    }

    /**
     * Vérifie si les recherches doivent être loggées
     */
    public static function shouldLogSearches(): bool
    {
        return config('public_portal.search.log_searches', true);
    }

    /**
     * Retourne le nombre maximum de résultats de recherche
     */
    public static function getMaxSearchResults(): int
    {
        return config('public_portal.search.max_search_results', 100);
    }

    /**
     * Retourne la limite de taux par minute
     */
    public static function getRateLimitPerMinute(): int
    {
        return config('public_portal.security.rate_limit_per_minute', 60);
    }

    /**
     * Retourne la durée de session en minutes
     */
    public static function getSessionLifetime(): int
    {
        return config('public_portal.security.session_lifetime', 120);
    }

    /**
     * Retourne la longueur minimale du mot de passe
     */
    public static function getPasswordMinLength(): int
    {
        return config('public_portal.security.password_min_length', 8);
    }

    /**
     * Retourne l'email de l'administrateur
     */
    public static function getAdminEmail(): string
    {
        return config('public_portal.notifications.admin_email', 'admin@example.com');
    }

    /**
     * Retourne l'email d'expédition
     */
    public static function getFromEmail(): string
    {
        return config('public_portal.notifications.from_email', 'noreply@example.com');
    }

    /**
     * Retourne le nom d'expédition
     */
    public static function getFromName(): string
    {
        return config('public_portal.notifications.from_name', 'Portail Public');
    }

    /**
     * Vérifie si un domaine email est autorisé
     */
    public static function isEmailDomainAllowed(string $email): bool
    {
        $allowedDomains = config('public_portal.registration.allowed_domains', '');

        if (empty($allowedDomains)) {
            return true; // Tous les domaines sont autorisés
        }

        $domain = substr(strrchr($email, "@"), 1);
        $allowedDomainsArray = explode(',', $allowedDomains);

        return in_array($domain, array_map('trim', $allowedDomainsArray));
    }

    /**
     * Vérifie si un utilisateur peut s'inscrire à un événement
     */
    public static function canRegisterForEvent(\App\Models\PublicEvent $event): bool
    {
        $deadlineHours = self::getEventRegistrationDeadlineHours();
        return $event->start_date > now()->addHours($deadlineHours);
    }

    /**
     * Vérifie si un utilisateur peut annuler son inscription à un événement
     */
    public static function canCancelEventRegistration(\App\Models\PublicEvent $event): bool
    {
        $deadlineHours = self::getEventCancellationDeadlineHours();
        return $event->start_date > now()->addHours($deadlineHours);
    }
}
