<?php

namespace Database\Seeders\Workplaces;

use Illuminate\Database\Seeder;
use App\Models\Workplace;
use App\Models\WorkplaceMember;
use App\Models\WorkplaceFolder;
use App\Models\WorkplaceDocument;
use App\Models\WorkplaceActivity;
use App\Models\WorkplaceInvitation;
use App\Models\WorkplaceBookmark;
use App\Models\WorkplaceTemplate;
use App\Models\WorkplaceCategory;
use App\Models\RecordDigitalFolder;
use App\Models\RecordDigitalDocument;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Support\Str;

class WorkplaceDataSeeder extends Seeder
{
    /**
     * Seed test data for the Workplaces module.
     * Creates workplaces, members, shared content, invitations, bookmarks, templates.
     * Idempotent: uses firstOrCreate/updateOrCreate.
     */
    public function run(): void
    {
        $this->command->info('🏢 Seeding Workplaces module test data...');

        $users = User::take(4)->get();
        if ($users->isEmpty()) {
            $this->command->warn('⚠️  No users found. Run SuperAdminSeeder first.');
            return;
        }
        $user = $users->first();
        $org = Organisation::first();
        $category = WorkplaceCategory::first();

        if (!$org || !$category) {
            $this->command->warn('⚠️  Missing organisations or categories. Run OrganisationSeeder + WorkplaceCategorySeeder first.');
            return;
        }

        $categories = WorkplaceCategory::take(5)->get();

        // --- 1. Workplace Templates ---
        $templates = [
            ['code' => 'TPL-PROJECT', 'name' => 'Projet collaboratif', 'description' => 'Template pour la gestion de projets d\'équipe', 'icon' => 'folder-open', 'category' => 'project', 'is_system' => true,
                'default_settings' => ['notifications' => true, 'auto_archive' => false],
                'default_structure' => ['folders' => ['Documents', 'Livrables', 'Réunions']],
                'default_permissions' => ['can_create_folders' => true, 'can_create_documents' => true, 'can_delete' => false]],
            ['code' => 'TPL-ARCHIVE', 'name' => 'Espace d\'archivage', 'description' => 'Template pour le travail d\'archivage collaboratif', 'icon' => 'archive', 'category' => 'archive', 'is_system' => true,
                'default_settings' => ['notifications' => true, 'versioning' => true],
                'default_structure' => ['folders' => ['Bordereaux', 'Instruments de recherche', 'Numérisations']],
                'default_permissions' => ['can_create_folders' => true, 'can_create_documents' => true, 'can_delete' => true]],
            ['code' => 'TPL-ADMIN', 'name' => 'Espace administratif', 'description' => 'Template pour les activités administratives courantes', 'icon' => 'briefcase', 'category' => 'admin', 'is_system' => false,
                'default_settings' => ['notifications' => false],
                'default_structure' => ['folders' => ['Notes de service', 'Procédures', 'Modèles']],
                'default_permissions' => ['can_create_folders' => false, 'can_create_documents' => true, 'can_delete' => false]],
        ];

        foreach ($templates as $tplData) {
            WorkplaceTemplate::firstOrCreate(
                ['code' => $tplData['code']],
                array_merge($tplData, [
                    'is_active' => true,
                    'usage_count' => rand(0, 15),
                    'display_order' => 0,
                    'created_by' => $user->id,
                ])
            );
        }

        // --- 2. Workplaces ---
        $workplaces = [
            ['code' => 'WS-NUMERISATION', 'name' => 'Projet Numérisation 2026', 'description' => 'Espace de travail pour le projet de numérisation des archives historiques', 'is_public' => false, 'status' => 'active', 'max_members' => 10, 'category_id' => $categories[0]->id],
            ['code' => 'WS-VERSEMENT-DRH', 'name' => 'Versements DRH', 'description' => 'Espace pour coordonner les versements d\'archives de la DRH', 'is_public' => false, 'status' => 'active', 'max_members' => 5, 'category_id' => $categories->count() > 1 ? $categories[1]->id : $categories[0]->id],
            ['code' => 'WS-GENERAL', 'name' => 'Documentation générale', 'description' => 'Espace public pour le partage de documents de référence', 'is_public' => true, 'status' => 'active', 'max_members' => 50, 'category_id' => $categories->count() > 2 ? $categories[2]->id : $categories[0]->id],
            ['code' => 'WS-FORMATION', 'name' => 'Formation nouvelles recrues', 'description' => 'Matériel de formation et guides utilisateur', 'is_public' => true, 'status' => 'active', 'max_members' => 20, 'category_id' => $categories->count() > 3 ? $categories[3]->id : $categories[0]->id],
            ['code' => 'WS-ARCHIVE-2025', 'name' => 'Projet classement 2025', 'description' => 'Espace archivé — projet de reclassement terminé', 'is_public' => false, 'status' => 'archived', 'max_members' => 8, 'category_id' => $categories[0]->id],
        ];

        $createdWorkplaces = [];
        foreach ($workplaces as $wsData) {
            $ws = Workplace::firstOrCreate(
                ['code' => $wsData['code']],
                array_merge($wsData, [
                    'organisation_id' => $org->id,
                    'owner_id' => $user->id,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                    'settings' => ['notifications' => true],
                    'allow_external_sharing' => false,
                    'max_storage_mb' => 1024,
                    'start_date' => now()->subMonths(rand(1, 6)),
                    'archived_at' => $wsData['status'] === 'archived' ? now()->subDays(30) : null,
                ])
            );
            $createdWorkplaces[] = $ws;
        }

        // --- 3. Workplace Members ---
        $roles = ['owner', 'admin', 'editor', 'viewer'];
        foreach ($createdWorkplaces as $wi => $ws) {
            foreach ($users as $ui => $u) {
                $role = $ui === 0 ? 'owner' : $roles[min($ui, count($roles) - 1)];
                WorkplaceMember::firstOrCreate(
                    ['workplace_id' => $ws->id, 'user_id' => $u->id],
                    [
                        'role' => $role,
                        'can_create_folders' => in_array($role, ['owner', 'admin', 'editor']),
                        'can_create_documents' => in_array($role, ['owner', 'admin', 'editor']),
                        'can_delete' => in_array($role, ['owner', 'admin']),
                        'can_share' => in_array($role, ['owner', 'admin']),
                        'can_invite' => in_array($role, ['owner', 'admin']),
                        'notify_on_new_content' => true,
                        'notify_on_mentions' => true,
                        'notify_on_updates' => $role !== 'viewer',
                        'invited_by' => $user->id,
                        'joined_at' => now()->subDays(rand(10, 60)),
                        'last_activity_at' => now()->subHours(rand(1, 72)),
                    ]
                );
            }
        }

        // --- 4. Workplace Folders & Documents ---
        $folder = RecordDigitalFolder::first();
        $document = RecordDigitalDocument::first();

        if ($folder) {
            foreach (array_slice($createdWorkplaces, 0, 3) as $ws) {
                WorkplaceFolder::firstOrCreate(
                    ['workplace_id' => $ws->id, 'folder_id' => $folder->id],
                    [
                        'shared_by' => $user->id,
                        'shared_at' => now()->subDays(rand(1, 20)),
                        'share_note' => 'Dossier partagé pour collaboration',
                        'access_level' => 'edit',
                        'is_pinned' => $ws->id === $createdWorkplaces[0]->id,
                        'display_order' => 1,
                    ]
                );
            }
        }

        if ($document) {
            foreach (array_slice($createdWorkplaces, 0, 3) as $ws) {
                WorkplaceDocument::firstOrCreate(
                    ['workplace_id' => $ws->id, 'document_id' => $document->id],
                    [
                        'shared_by' => $user->id,
                        'shared_at' => now()->subDays(rand(1, 15)),
                        'share_note' => 'Document de référence',
                        'access_level' => 'view',
                        'is_featured' => false,
                        'views_count' => rand(0, 20),
                    ]
                );
            }
        }

        // --- 5. Workplace Activities ---
        $activityTypes = ['member_joined', 'document_shared', 'folder_created', 'member_invited', 'settings_updated', 'document_viewed'];
        foreach ($createdWorkplaces as $ws) {
            foreach (array_slice($activityTypes, 0, rand(2, 4)) as $type) {
                WorkplaceActivity::firstOrCreate(
                    ['workplace_id' => $ws->id, 'activity_type' => $type, 'user_id' => $user->id],
                    [
                        'description' => "Activité '$type' dans l'espace {$ws->name}",
                        'metadata' => ['workspace_code' => $ws->code],
                        'ip_address' => '127.0.0.1',
                    ]
                );
            }
        }

        // --- 6. Workplace Invitations ---
        $invitations = [
            ['email' => 'invite1@example.com', 'proposed_role' => 'editor', 'status' => 'pending', 'workplace_id' => $createdWorkplaces[0]->id],
            ['email' => 'invite2@example.com', 'proposed_role' => 'viewer', 'status' => 'pending', 'workplace_id' => $createdWorkplaces[0]->id],
            ['email' => 'invite3@example.com', 'proposed_role' => 'admin', 'status' => 'accepted', 'workplace_id' => $createdWorkplaces[1]->id],
            ['email' => 'invite4@example.com', 'proposed_role' => 'editor', 'status' => 'expired', 'workplace_id' => $createdWorkplaces[2]->id],
        ];

        foreach ($invitations as $inv) {
            WorkplaceInvitation::firstOrCreate(
                ['email' => $inv['email'], 'workplace_id' => $inv['workplace_id']],
                array_merge($inv, [
                    'invited_by' => $user->id,
                    'message' => 'Vous êtes invité à rejoindre cet espace de travail.',
                    'token' => Str::random(64),
                    'expires_at' => $inv['status'] === 'expired' ? now()->subDays(7) : now()->addDays(7),
                    'responded_at' => $inv['status'] === 'accepted' ? now()->subDays(2) : null,
                ])
            );
        }

        // --- 7. Workplace Bookmarks ---
        if ($folder) {
            WorkplaceBookmark::firstOrCreate(
                ['workplace_id' => $createdWorkplaces[0]->id, 'user_id' => $user->id, 'bookmarkable_type' => RecordDigitalFolder::class, 'bookmarkable_id' => $folder->id],
                ['note' => 'Dossier important à suivre']
            );
        }
        if ($document) {
            WorkplaceBookmark::firstOrCreate(
                ['workplace_id' => $createdWorkplaces[0]->id, 'user_id' => $user->id, 'bookmarkable_type' => RecordDigitalDocument::class, 'bookmarkable_id' => $document->id],
                ['note' => 'Document de référence clé']
            );
        }

        $this->command->info('✅ Workplaces module: ' . count($createdWorkplaces) . ' workplaces with members, content, invitations seeded.');
    }
}
