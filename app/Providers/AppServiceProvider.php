<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use App\Services\PolicyService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }




    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Laravel découvrira automatiquement les policies selon les conventions de nommage
        // Exemple : User -> UserPolicy, Record -> RecordPolicy, etc.
        // Enregistrement manuel seulement si nécessaire pour des cas spéciaux

        // Enregistrer nos Gates personnalisés
        PolicyService::registerGates();

        // Enregistrer les observers
        \App\Models\TaskAssignment::observe(\App\Observers\TaskAssignmentObserver::class);

        $this->handleLocale();

        // Add the SetLocale middleware to the web group
        Route::pushMiddlewareToGroup('web', \App\Http\Middleware\SetLocale::class);

        Auth::macro('currentOrganisationId', function () {
            return Auth::check() ? Auth::user()->current_organisation_id : null;
        });

    }
    protected function handleLocale(): void
    {
        if (session()->has('locale')) {
            App::setLocale(session()->get('locale'));
        }
    }






}
