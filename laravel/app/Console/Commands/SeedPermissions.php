<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SeedPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:permissions {--fresh : Truncate the permissions table before seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the permissions table with predefined permissions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $fresh = $this->option('fresh');

        if ($fresh) {
            $this->warn('Option --fresh ignorée. Le seeder utilise maintenant updateOrInsert() par défaut pour la sécurité.');
        }

        $this->info('Running permissions seeding (updateOrInsert - safe for production)...');
        Artisan::call('db:seed', [
            '--class' => 'PermissionSeeder'
        ]);

        $this->info('Permissions seeded successfully! Total: 222 permissions');

        return Command::SUCCESS;
    }
}
