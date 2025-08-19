<?php

namespace App\Providers;

use App\Models\PublicDocumentRequest;
use App\Models\PublicEvent;
use App\Policies\PublicDocumentRequestPolicy;
use App\Policies\PublicEventPolicy;
use App\Services\PolicyService;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

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
        \App\Models\Term::class => \App\Policies\TermPolicy::class,
        \App\Models\Record::class => \App\Policies\RecordPolicy::class,
        \App\Models\Mail::class => \App\Policies\MailPolicy::class,
        \App\Models\Slip::class => \App\Policies\SlipPolicy::class,
        \App\Models\SlipRecord::class => \App\Policies\SlipRecordPolicy::class,
    // \App\Models\Tool model not found; mapping removed
    // \App\Models\Transferring model not found; mapping removed
        \App\Models\Task::class => \App\Policies\TaskPolicy::class,
    // \App\Models\Deposit model not found; mapping removed
        \App\Models\Dolly::class => \App\Policies\DollyPolicy::class,
        \App\Models\Container::class => \App\Policies\ContainerPolicy::class,
        \App\Models\Retention::class => \App\Policies\RetentionPolicy::class,
        \App\Models\Law::class => \App\Policies\LawPolicy::class,
        \App\Models\Communicability::class => \App\Policies\CommunicabilityPolicy::class,
        \App\Models\Reservation::class => \App\Policies\ReservationPolicy::class,
    // \App\Models\Report model not found; mapping removed
        \App\Models\Event::class => \App\Policies\EventPolicy::class,
        \App\Models\Log::class => \App\Policies\LogPolicy::class,
        \App\Models\Backup::class => \App\Policies\BackupPolicy::class,
        \App\Models\Communication::class => \App\Policies\CommunicationPolicy::class,
        \App\Models\BulletinBoard::class => \App\Policies\BulletinBoardPolicy::class,
        \App\Models\Batch::class => \App\Policies\BatchPolicy::class,
        \App\Models\Building::class => \App\Policies\BuildingPolicy::class,
        \App\Models\Floor::class => \App\Policies\FloorPolicy::class,
        \App\Models\Room::class => \App\Policies\RoomPolicy::class,
        \App\Models\Shelf::class => \App\Policies\ShelfPolicy::class,
        \App\Models\Setting::class => \App\Policies\SettingPolicy::class,
    // \App\Models\PublicPortal model not found; mapping removed
        \App\Models\Post::class => \App\Policies\PostPolicy::class,
    // \App\Models\Ai model and AiPolicy not found; mapping removed
    // \App\Models\Barcode model not found; mapping removed

        // Workflow module policies
        \App\Models\WorkflowTemplate::class => \App\Policies\WorkflowTemplatePolicy::class,
        \App\Models\WorkflowStep::class => \App\Policies\WorkflowStepPolicy::class,
        \App\Models\WorkflowInstance::class => \App\Policies\WorkflowInstancePolicy::class,
        \App\Models\WorkflowStepInstance::class => \App\Policies\WorkflowStepInstancePolicy::class,
        \App\Models\TaskComment::class => \App\Policies\TaskCommentPolicy::class,
        \App\Models\TaskAssignment::class => \App\Policies\TaskAssignmentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Enregistrer les Gates personnalisés avec Spatie Permission
        PolicyService::registerGates();
    }
}
