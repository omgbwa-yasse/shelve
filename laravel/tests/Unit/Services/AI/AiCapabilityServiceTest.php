<?php

namespace Tests\Unit\Services\AI;

use App\Models\Organisation;
use App\Models\Permission;
use App\Models\User;
use App\Services\AI\AiCapabilityService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Traduction des permissions effectives en résumé pour le prompt système de
 * l'assistant IA — voir exigence utilisateur du 2026-08-05 ("le chatbot ne
 * peut que voir les actions autorisées de son profil").
 */
class AiCapabilityServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_regroupe_les_permissions_par_ressource_avec_libelles_lisibles(): void
    {
        $organisation = Organisation::factory()->create();
        $user = User::factory()->forOrganisation($organisation)->create();

        $viewAny = Permission::firstOrCreate(['name' => 'project_viewAny'], ['category' => 'projects', 'description' => 'test']);
        $create = Permission::firstOrCreate(['name' => 'project_create'], ['category' => 'projects', 'description' => 'test']);
        $user->permissions()->syncWithoutDetaching([$viewAny->id, $create->id]);

        $summary = app(AiCapabilityService::class)->summaryFor($user->fresh());

        $this->assertStringContainsString('project', $summary);
        $this->assertStringContainsString('consulter', $summary);
        $this->assertStringContainsString('créer', $summary);
    }

    public function test_signale_l_absence_de_permission(): void
    {
        $organisation = Organisation::factory()->create();
        $user = User::factory()->forOrganisation($organisation)->create();

        $summary = app(AiCapabilityService::class)->summaryFor($user->fresh());

        $this->assertStringContainsString("ne dispose d'aucune permission", $summary);
    }
}
