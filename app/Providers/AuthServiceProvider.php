<?php

namespace App\Providers;

use App\Models\PublicDocumentRequest;
use App\Models\PublicEvent;
use App\Policies\PublicDocumentRequestPolicy;
use App\Policies\PublicEventPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        PublicDocumentRequest::class => PublicDocumentRequestPolicy::class,
        PublicEvent::class => PublicEventPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
