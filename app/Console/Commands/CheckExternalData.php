<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ExternalOrganization;
use App\Models\ExternalContact;

class CheckExternalData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'external:check-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifier les données des contacts et organisations externes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== ORGANISATIONS EXTERNES ===');
        $organizations = ExternalOrganization::all();
        
        foreach ($organizations as $org) {
            $this->line("📋 {$org->name} ({$org->city})");
            $this->line("   Email: {$org->email}");
            $this->line("   Téléphone: {$org->phone}");
            $this->line("   Statut: " . ($org->is_verified ? 'Vérifié' : 'Non vérifié'));
            $this->newLine();
        }

        $this->info('=== CONTACTS EXTERNES ===');
        $contacts = ExternalContact::with('organization')->get();
        
        foreach ($contacts as $contact) {
            $this->line("👤 {$contact->full_name}");
            $this->line("   Position: {$contact->position}");
            $this->line("   Email: {$contact->email}");
            $this->line("   Téléphone: {$contact->phone}");
            
            if ($contact->organization) {
                $this->line("   Organisation: {$contact->organization->name}");
                $this->line("   Contact principal: " . ($contact->is_primary_contact ? 'Oui' : 'Non'));
            } else {
                $this->line("   Contact indépendant");
            }
            
            $this->line("   Statut: " . ($contact->is_verified ? 'Vérifié' : 'Non vérifié'));
            $this->newLine();
        }

        $this->info('=== RÉSUMÉ ===');
        $this->table(
            ['Type', 'Nombre'],
            [
                ['Total organisations', $organizations->count()],
                ['Total contacts', $contacts->count()],
                ['Contacts avec organisation', $contacts->whereNotNull('external_organization_id')->count()],
                ['Contacts indépendants', $contacts->whereNull('external_organization_id')->count()],
            ]
        );

        return Command::SUCCESS;
    }
}
