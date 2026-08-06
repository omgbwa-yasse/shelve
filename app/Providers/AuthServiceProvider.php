<?php

namespace App\Providers;

use App\Models\PublicDocumentRequest;
use App\Models\PublicEvent;
use App\Policies\PublicDocumentRequestPolicy;
use App\Policies\PublicEventPolicy;
use App\Services\PolicyService;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
        protected $policies = [
        // Existing policies
        \App\Models\PublicDocumentRequest::class => \App\Policies\PublicDocumentRequestPolicy::class,
        \App\Models\PublicEvent::class => \App\Policies\PublicEventPolicy::class,

        // Auto-generated policies
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        \App\Models\Role::class => \App\Policies\RolePolicy::class,
        \App\Models\Organisation::class => \App\Policies\OrganisationPolicy::class,
        \App\Models\Activity::class => \App\Policies\ActivityPolicy::class,
        \App\Models\Author::class => \App\Policies\AuthorPolicy::class,
    \App\Models\Language::class => \App\Policies\LanguagePolicy::class,
        \App\Models\RecordPhysical::class => \App\Policies\RecordPolicy::class,
        \App\Models\Mail::class => \App\Policies\MailPolicy::class,
        \App\Models\Slip::class => \App\Policies\SlipPolicy::class,
        \App\Models\SlipRecord::class => \App\Policies\SlipRecordPolicy::class,
        \App\Models\Dolly::class => \App\Policies\DollyPolicy::class,
        \App\Models\Container::class => \App\Policies\ContainerPolicy::class,
        \App\Models\Retention::class => \App\Policies\RetentionPolicy::class,
        \App\Models\Law::class => \App\Policies\LawPolicy::class,
        // Ajoutées au portage de D01 : ces référentiels étaient exposés par le
        // back-office sans politique d'autorisation (risque R04).
        \App\Models\LawArticle::class => \App\Policies\LawArticlePolicy::class,
        \App\Models\Sort::class => \App\Policies\SortPolicy::class,
        \App\Models\Keyword::class => \App\Policies\KeywordPolicy::class,
        \App\Models\Communicability::class => \App\Policies\CommunicabilityPolicy::class,
        \App\Models\Reservation::class => \App\Policies\ReservationPolicy::class,
        \App\Models\Log::class => \App\Policies\LogPolicy::class,
        \App\Models\Backup::class => \App\Policies\BackupPolicy::class,
        \App\Models\Communication::class => \App\Policies\CommunicationPolicy::class,
        \App\Models\Batch::class => \App\Policies\BatchPolicy::class,
        \App\Models\Building::class => \App\Policies\BuildingPolicy::class,
        \App\Models\Floor::class => \App\Policies\FloorPolicy::class,
        \App\Models\Room::class => \App\Policies\RoomPolicy::class,
        \App\Models\Shelf::class => \App\Policies\ShelfPolicy::class,
        \App\Models\Setting::class => \App\Policies\SettingPolicy::class,
        \App\Models\SettingCategory::class => \App\Policies\SettingCategoryPolicy::class,
        \App\Models\ReferenceList::class => \App\Policies\ReferenceListPolicy::class,
        \App\Models\ReferenceValue::class => \App\Policies\ReferenceValuePolicy::class,
        \App\Models\AuthorContact::class => \App\Policies\AuthorContactPolicy::class,
        \App\Models\ExternalContact::class => \App\Policies\ExternalContactPolicy::class,
        \App\Models\ExternalOrganization::class => \App\Policies\ExternalOrganizationPolicy::class,
        \App\Models\ContainerProperty::class => \App\Policies\ContainerPropertyPolicy::class,
        \App\Models\ContainerStatus::class => \App\Policies\ContainerStatusPolicy::class,
        \App\Models\WorkflowDefinition::class => \App\Policies\WorkflowDefinitionPolicy::class,
        \App\Models\WorkflowInstance::class => \App\Policies\WorkflowInstancePolicy::class,
        \App\Models\DeclassementList::class => \App\Policies\DeclassementListPolicy::class,
        \App\Models\RecordReactivation::class => \App\Policies\RecordReactivationPolicy::class,
        \App\Models\Project::class => \App\Policies\ProjectPolicy::class,
        \App\Models\Objective::class => \App\Policies\ObjectivePolicy::class,
        \App\Models\Kpi::class => \App\Policies\KpiPolicy::class,
        \App\Models\AiRoutine::class => \App\Policies\AiRoutinePolicy::class,
    // \App\Models\PublicPortal model not found; mapping removed
    // \App\Models\Ai model and AiPolicy not found; mapping removed
    // \App\Models\Barcode model not found; mapping removed
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Superadmins can perform any action, even when a specific ability
        // was not registered dynamically yet.
        Gate::before(function ($user, string $ability) {
            if (method_exists($user, 'hasRole') && $user->hasRole('superadmin')) {
                return true;
            }

            return null;
        });

        // Enregistrer les Gates personnalisés avec Spatie Permission
        PolicyService::registerGates();
    }
}
