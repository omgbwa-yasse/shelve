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
            $this->info('Running fresh permissions seeding (will truncate existing data)...');
            Artisan::call('db:seed', [
                '--class' => 'PermissionSeeder'
            ]);
        } else {
            $this->info('Running permissions update seeding (will preserve existing data)...');
            Artisan::call('db:seed', [
                '--class' => 'PermissionUpdateSeeder'
            ]);
        }

        $this->info('Permissions seeded successfully!');
        
        return Command::SUCCESS;
    }
}
