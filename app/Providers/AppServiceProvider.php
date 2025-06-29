<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use App\Models\Record;
use App\Policies\RecordPolicy;
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
        Gate::policy(Record::class, RecordPolicy::class);
        
        // Enregistrer nos Gates personnalisés
        PolicyService::registerGates();
        
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
