<?php

namespace Database\Seeders\Mails;

use App\Enums\MailStatusEnum;
use App\Models\Mail;
use App\Models\Organisation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Jeu de démonstration explicite. Il n’est jamais appelé par DatabaseSeeder et
 * refuse de s’exécuter hors environnement local/testing.
 */
class MailCircuitDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('MailCircuitDemoSeeder ignoré hors environnement local/testing.');

            return;
        }

        $roles = collect(['agent', 'responsable', 'directeur', 'DG'])
            ->mapWithKeys(fn (string $name) => [$name => Role::firstOrCreate(['name' => $name], ['description' => 'Rôle du circuit courrier'])]);

        $dg = Organisation::updateOrCreate(['code' => 'DG-DEMO'], ['name' => 'Direction Générale - Démonstration', 'parent_id' => null]);
        $operations = Organisation::updateOrCreate(['code' => 'DOP-DEMO'], ['name' => 'Direction des Opérations', 'parent_id' => $dg->id]);
        $courrier = Organisation::updateOrCreate(['code' => 'SC-DEMO'], ['name' => 'Service Courrier', 'parent_id' => $operations->id]);
        $finance = Organisation::updateOrCreate(['code' => 'FIN-DEMO'], ['name' => 'Direction des Finances - Démonstration', 'parent_id' => $dg->id]);
        $legal = Organisation::updateOrCreate(['code' => 'JUR-DEMO'], ['name' => 'Direction Juridique et Conformité', 'parent_id' => $dg->id]);
        $dsi = Organisation::updateOrCreate(['code' => 'DSI-DEMO'], ['name' => 'Direction des Systèmes d’Information', 'parent_id' => $dg->id]);

        $users = [
            'agent' => $this->user('agent.courrier@example.com', 'Amina', 'Nkoa', $courrier),
            'n1' => $this->user('responsable.courrier@example.com', 'Martin', 'Essomba', $courrier),
            'n2' => $this->user('directeur.operations@example.com', 'Clarisse', 'Mballa', $operations),
            'dg' => $this->user('dg.demo@example.com', 'Jean', 'Ndongo', $dg),
            'finance' => $this->user('finance.demo@example.com', 'Sarah', 'Etame', $finance),
            'legal' => $this->user('juridique.demo@example.com', 'Alain', 'Manga', $legal),
            'dsi' => $this->user('dsi.demo@example.com', 'Boris', 'Tchana', $dsi),
            'alternate' => $this->user('suppleant.demo@example.com', 'Nadine', 'Biya', $operations),
        ];

        $assignments = [
            ['user' => 'agent', 'org' => $courrier, 'role' => 'agent'],
            ['user' => 'n1', 'org' => $courrier, 'role' => 'responsable'],
            ['user' => 'n2', 'org' => $operations, 'role' => 'directeur'],
            ['user' => 'dg', 'org' => $dg, 'role' => 'DG'],
            ['user' => 'finance', 'org' => $finance, 'role' => 'directeur'],
            ['user' => 'legal', 'org' => $legal, 'role' => 'directeur'],
            ['user' => 'dsi', 'org' => $dsi, 'role' => 'directeur'],
            ['user' => 'alternate', 'org' => $operations, 'role' => 'responsable'],
        ];

        foreach ($assignments as $assignment) {
            DB::table('user_organisation_role')->updateOrInsert(
                ['user_id' => $users[$assignment['user']]->id, 'organisation_id' => $assignment['org']->id],
                ['role_id' => $roles[$assignment['role']]->id, 'creator_id' => $users['dg']->id, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $typologyId = DB::table('mail_typologies')->value('id');

        if (! $typologyId) {
            throw new \RuntimeException('Aucune typologie de courrier disponible. Exécutez d’abord MailSystemSeeder.');
        }

        Mail::withoutSyncingToSearch(function () use ($users, $courrier, $finance, $legal, $dsi, $typologyId) {
            $this->mail('DEMO-CIR-FACTURE-001', 'Facture fournisseur - livraison carburant', $users['agent'], $courrier, $finance, $typologyId);
            $this->mail('DEMO-CIR-CONTRAT-001', 'Contrat de maintenance informatique', $users['agent'], $courrier, $dsi, $typologyId);
            $this->mail('DEMO-CIR-EXTERNE-001', 'Réponse à une demande de la tutelle', $users['agent'], $courrier, $legal, $typologyId);
            $this->mail('DEMO-CIR-CYBER-001', 'Signalement d’un incident de cybersécurité', $users['agent'], $courrier, $dsi, $typologyId);
            $this->mail('DEMO-CIR-SENSIBLE-001', 'Note confidentielle relative à un litige', $users['agent'], $courrier, $legal, $typologyId);
            $this->mail('DEMO-CIR-CUSTOM-001', 'Demande nécessitant un circuit personnalisé', $users['agent'], $courrier, $finance, $typologyId);
        });

        $this->command?->info('Démonstration circuits courrier créée. Mot de passe de tous les comptes : Demo2026!');
    }

    private function user(string $email, string $name, string $surname, Organisation $organisation): User
    {
        return User::updateOrCreate(['email' => $email], [
            'name' => $name,
            'surname' => $surname,
            'password' => Hash::make('Demo2026!'),
            'birthday' => '1990-01-01',
            'current_organisation_id' => $organisation->id,
        ]);
    }

    private function mail(string $code, string $name, User $sender, Organisation $senderOrganisation, Organisation $recipientOrganisation, int $typologyId): void
    {
        Mail::updateOrCreate(['code' => $code], [
            'name' => $name,
            'date' => now(),
            'description' => 'Courrier de démonstration destiné à tester les circuits de validation métier.',
            'status' => MailStatusEnum::DRAFT,
            'mail_type' => Mail::TYPE_OUTGOING,
            'typology_id' => $typologyId,
            'sender_user_id' => $sender->id,
            'sender_organisation_id' => $senderOrganisation->id,
            'recipient_organisation_id' => $recipientOrganisation->id,
            'is_archived' => false,
        ]);
    }
}
