<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateCommunicationsPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:update-communications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Met à jour les permissions du module communications';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Mise à jour des permissions du module communications...');

        $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\CommunicationsPermissionsSeeder',
            '--force' => true,
        ]);

        $this->info('Permissions mises à jour avec succès!');
        return Command::SUCCESS;
    }
}
