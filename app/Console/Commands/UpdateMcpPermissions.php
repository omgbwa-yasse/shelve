<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateMcpPermissions extends Command
{
    /**
     * Le nom et la signature de la commande console.
     *
     * @var string
     */
    protected $signature = 'permissions:update-mcp';

    /**
     * La description de la commande console.
     *
     * @var string
     */
    protected $description = 'Met à jour les permissions pour les fonctionnalités MCP/IA';

    /**
     * Exécuter la commande console.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Mise à jour des permissions MCP/IA...');

        // Exécuter le seeder
        $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\UpdateRecordsPermissionsSeeder',
        ]);

        $this->info('✅ Permissions MCP/IA mises à jour avec succès!');

        return Command::SUCCESS;
    }
}
