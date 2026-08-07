<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use App\Services\PolicyService;
use App\Services\SettingService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Vite;
use App\Models\Slip;
use App\Observers\SlipObserver;
use App\Models\Task;
use App\Observers\TaskObserver;
use App\Models\Workplace;
use Illuminate\Pagination\Paginator;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SettingService::class, function () {
            return new SettingService();
        });

        // Désactiver certains services en production pour améliorer les performances
        if ($this->app->environment('production')) {
            $this->app['config']['app.debug'] = false;
            $this->app['config']['logging.default'] = 'daily';
        }
    }




    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Schema::defaultStringLength(191);

        // Route model binding des workplaces par code (slug) OU par id : les
        // URLs passent par `/workplace/{code}` (ex. rh, sia2019, dg-sg) tout en
        // gardant la compatibilité avec les anciens liens `/workplaces/{id}`.
        // L'isolation multi-organisation reste portée par les contrôleurs.
        Route::bind('workplace', function (string $value) {
            return Workplace::query()
                ->where('code', $value)
                ->orWhere('id', $value)
                ->firstOrFail();
        });

        // Enregistrer les Observers
        Slip::observe(SlipObserver::class);
        Task::observe(TaskObserver::class);

        // Laravel découvrira automatiquement les policies selon les conventions de nommage
        // Exemple : User -> UserPolicy, Record -> RecordPolicy, etc.
        // Enregistrement manuel seulement si nécessaire pour des cas spéciaux

        // Enregistrer nos Gates personnalisés
        PolicyService::registerGates();

        // Outils sandbox Python pour l'assistant IA (D14) : enregistrés sur le
        // singleton AiBridge pour la boucle tool-aware (`chatWithTools`).
        $this->registerSandboxTools();

        $this->handleLocale();

        // Add the SetLocale middleware to the web group
        Route::pushMiddlewareToGroup('web', \App\Http\Middleware\SetLocale::class);

        Auth::macro('currentOrganisationId', function () {
            return Auth::check() ? Auth::user()->current_organisation_id : null;
        });
        // Verrouillage des assets: ignorer le serveur Vite en prod ou si forcé
        if (config('assets.force_build', false) || $this->app->environment('production')) {
            // Pointez vers un hot file inexistant pour désactiver le mode hot
            Vite::useHotFile(storage_path('framework/disable-vite-hot'));
            // Assurez-vous d'utiliser le répertoire de build par défaut
            Vite::useBuildDirectory('build');
        }

    }
    protected function handleLocale(): void
    {
        if (session()->has('locale')) {
            App::setLocale(session()->get('locale'));
        }
    }

    /**
     * Enregistre les outils sandbox Python (open/write/run/close) sur le
     * singleton AiBridge utilisé par la boucle tool-aware.
     */
    protected function registerSandboxTools(): void
    {
        $sandbox = app(\App\Services\AI\SandboxService::class);

        \AiBridge\Facades\AiBridge::registerTool(new \App\Services\AI\Sandbox\Tools\SandboxOpenTool($sandbox));
        \AiBridge\Facades\AiBridge::registerTool(new \App\Services\AI\Sandbox\Tools\SandboxWriteTool($sandbox));
        \AiBridge\Facades\AiBridge::registerTool(new \App\Services\AI\Sandbox\Tools\SandboxRunTool($sandbox));
        \AiBridge\Facades\AiBridge::registerTool(new \App\Services\AI\Sandbox\Tools\SandboxCloseTool($sandbox));
    }






}
