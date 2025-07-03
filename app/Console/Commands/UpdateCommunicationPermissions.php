<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateCommunicationPermissions extends Command
{
    protected $signature = 'permissions:update-communication';
    protected $description = 'Met à jour les permissions du module communications et les attribue au superadmin';

    public function handle()
    {
        $this->info('Mise à jour des permissions de communication...');
        
        // Exécuter le seeder pour mettre à jour les permissions
        $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\CommunicationPermissionsSeeder'
        ]);
        
        $this->info('Les permissions du module communications ont été mises à jour avec succès !');
        
        return Command::SUCCESS;
    }
}
