<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class OptimizeForProduction extends Command
{
    protected $signature = 'app:optimize-production';
    protected $description = 'Optimize the application for production';

    public function handle()
    {
        $this->info('Optimizing application for production...');

        // Cache configuration
        $this->call('config:cache');

        // Cache routes
        $this->call('route:cache');

        // Cache views
        $this->call('view:cache');

        // Cache events
        $this->call('event:cache');

        // Clear unnecessary caches
        $this->call('cache:clear');

        // Optimize composer autoloader
        exec('composer install --optimize-autoloader --no-dev');

        $this->info('Application optimized for production!');
    }
}
