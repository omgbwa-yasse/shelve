<?php

namespace App\Providers;


use Illuminate\Support\Facades\Gate;
use App\Models\Record;
use App\Policies\RecordPolicy;
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
    }



}



