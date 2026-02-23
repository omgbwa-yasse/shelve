<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workplace;
use App\Models\WorkplaceCategory;
use App\Models\Organisation;
use App\Models\RecordDigitalFolder;
use App\Models\RecordDigitalDocument;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WorkplaceContentSearchTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $organisation;
    protected $workplace;

    protected function setUp(): void
    {
        parent::setUp();

        // Retrieve Superadmin User
        $this->user = User::where('email', 'superadmin@example.com')->first();

        if (!$this->user) {
            $this->organisation = Organisation::firstOrCreate(
                ['code' => 'TEST-ORG'],
                ['name' => 'Test Organisation']
            );
            $this->user = User::create([
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'password' => bcrypt('superadmin'),
                'current_organisation_id' => $this->organisation->id,
                'birthday' => '1990-01-01',
            ]);
        } else {
            $this->organisation = Organisation::find($this->user->current_organisation_id);
            if (!$this->organisation) {
                $this->organisation = Organisation::firstOrCreate(
                    ['code' => 'TEST-ORG'],
                    ['name' => 'Test Organisation']
                );
                $this->user->current_organisation_id = $this->organisation->id;
                $this->user->save();
            }
        }

        $category = WorkplaceCategory::first();
        if (!$category) {
            $category = WorkplaceCategory::create([
                'name' => 'General',
                'code' => 'GEN-' . uniqid(),
                'is_active' => true,
            ]);
        }

        $this->workplace = Workplace::create([
            'name' => 'Test Workspace',
            'code' => 'WP-SEARCH-' . uniqid(),
            'category_id' => $category->id,
            'organisation_id' => $this->organisation->id,
            'owner_id' => $this->user->id,
            'created_by' => $this->user->id,
        ]);

        $this->workplace->members()->create([
            'user_id' => $this->user->id,
            'role' => 'owner',
            'joined_at' => now(),
        ]);
    }

    // ===================== SEARCH FOLDERS =====================

    public function test_search_folders_returns_json()
    {
        $this->actingAs($this->user);

        RecordDigitalFolder::create([
            'code' => 'FLD-SEARCH-' . uniqid(),
            'name' => 'Budget Annuel 2026',
            'description' => 'Dossier budget prévisionnel',
            'type_id' => 1,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
            'status' => 'active',
        ]);

        $response = $this->getJson(route('workplaces.content.searchFolders', $this->workplace) . '?q=Budget');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['name' => 'Budget Annuel 2026']);
    }

    public function test_search_folders_requires_minimum_2_characters()
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('workplaces.content.searchFolders', $this->workplace) . '?q=B');

        $response->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function test_search_folders_returns_empty_with_no_query()
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('workplaces.content.searchFolders', $this->workplace) . '?q=');

        $response->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function test_search_folders_matches_by_code()
    {
        $this->actingAs($this->user);

        $folder = RecordDigitalFolder::create([
            'code' => 'FLD-UNIQUE-XYZ',
            'name' => 'Dossier ABC',
            'type_id' => 1,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
            'status' => 'active',
        ]);

        $response = $this->getJson(route('workplaces.content.searchFolders', $this->workplace) . '?q=UNIQUE-XYZ');

        $response->assertStatus(200)
            ->assertJsonFragment(['code' => 'FLD-UNIQUE-XYZ']);
    }

    public function test_search_folders_matches_by_description()
    {
        $this->actingAs($this->user);

        RecordDigitalFolder::create([
            'code' => 'FLD-DESC-' . uniqid(),
            'name' => 'Dossier Standard',
            'description' => 'Contenu très spécifique comptabilité',
            'type_id' => 1,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
            'status' => 'active',
        ]);

        $response = $this->getJson(route('workplaces.content.searchFolders', $this->workplace) . '?q=comptabilité');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Dossier Standard']);
    }

    public function test_search_folders_excludes_already_shared()
    {
        $this->actingAs($this->user);

        $folder = RecordDigitalFolder::create([
            'code' => 'FLD-SHARED-' . uniqid(),
            'name' => 'Dossier Déjà Partagé',
            'type_id' => 1,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
            'status' => 'active',
        ]);

        // Share the folder
        $this->workplace->folders()->create([
            'folder_id' => $folder->id,
            'access_level' => 'view',
            'shared_by' => $this->user->id,
            'shared_at' => now(),
        ]);

        $response = $this->getJson(route('workplaces.content.searchFolders', $this->workplace) . '?q=Déjà Partagé');

        $response->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function test_search_folders_excludes_other_organisation()
    {
        $this->actingAs($this->user);

        $otherOrg = Organisation::create([
            'code' => 'OT' . rand(100, 999),
            'name' => 'Autre Organisation',
        ]);

        RecordDigitalFolder::create([
            'code' => 'FLD-OTHER-' . uniqid(),
            'name' => 'Dossier Autre Org',
            'type_id' => 1,
            'organisation_id' => $otherOrg->id,
            'creator_id' => $this->user->id,
            'status' => 'active',
        ]);

        $response = $this->getJson(route('workplaces.content.searchFolders', $this->workplace) . '?q=Autre Org');

        $response->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function test_search_folders_returns_expected_fields()
    {
        $this->actingAs($this->user);

        RecordDigitalFolder::create([
            'code' => 'FLD-FIELDS-' . uniqid(),
            'name' => 'Dossier Champs Complets',
            'description' => 'Description du dossier',
            'type_id' => 1,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
            'status' => 'active',
            'documents_count' => 5,
        ]);

        $response = $this->getJson(route('workplaces.content.searchFolders', $this->workplace) . '?q=Champs Complets');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'text', 'code', 'name', 'description', 'documents_count', 'status']
            ])
            ->assertJsonFragment(['documents_count' => 5]);
    }

    public function test_search_folders_limits_to_15_results()
    {
        $this->actingAs($this->user);

        for ($i = 1; $i <= 20; $i++) {
            RecordDigitalFolder::create([
                'code' => 'FLD-LIM-' . $i . '-' . uniqid(),
                'name' => 'Dossier Limite Test ' . $i,
                'type_id' => 1,
                'organisation_id' => $this->organisation->id,
                'creator_id' => $this->user->id,
                'status' => 'active',
            ]);
        }

        $response = $this->getJson(route('workplaces.content.searchFolders', $this->workplace) . '?q=Limite Test');

        $response->assertStatus(200);
        $this->assertLessThanOrEqual(15, count($response->json()));
    }

    // ===================== SEARCH DOCUMENTS =====================

    public function test_search_documents_returns_json()
    {
        $this->actingAs($this->user);

        $folder = RecordDigitalFolder::create([
            'code' => 'FLD-DOC-' . uniqid(),
            'name' => 'Dossier Parent',
            'type_id' => 1,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
            'status' => 'active',
        ]);

        RecordDigitalDocument::create([
            'code' => 'DOC-SEARCH-' . uniqid(),
            'name' => 'Rapport Financier 2026',
            'description' => 'Rapport annuel financier',
            'type_id' => 1,
            'folder_id' => $folder->id,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
            'status' => 'active',
        ]);

        $response = $this->getJson(route('workplaces.content.searchDocuments', $this->workplace) . '?q=Rapport');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['name' => 'Rapport Financier 2026']);
    }

    public function test_search_documents_requires_minimum_2_characters()
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('workplaces.content.searchDocuments', $this->workplace) . '?q=R');

        $response->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function test_search_documents_matches_by_code()
    {
        $this->actingAs($this->user);

        $folder = RecordDigitalFolder::create([
            'code' => 'FLD-DCODE-' . uniqid(),
            'name' => 'Parent',
            'type_id' => 1,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
            'status' => 'active',
        ]);

        RecordDigitalDocument::create([
            'code' => 'DOC-UNIQ-ABC123',
            'name' => 'Document Standard',
            'type_id' => 1,
            'folder_id' => $folder->id,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
            'status' => 'active',
        ]);

        $response = $this->getJson(route('workplaces.content.searchDocuments', $this->workplace) . '?q=UNIQ-ABC123');

        $response->assertStatus(200)
            ->assertJsonFragment(['code' => 'DOC-UNIQ-ABC123']);
    }

    public function test_search_documents_excludes_already_shared()
    {
        $this->actingAs($this->user);

        $folder = RecordDigitalFolder::create([
            'code' => 'FLD-DSHARE-' . uniqid(),
            'name' => 'Parent',
            'type_id' => 1,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
            'status' => 'active',
        ]);

        $document = RecordDigitalDocument::create([
            'code' => 'DOC-SHARED-' . uniqid(),
            'name' => 'Document Déjà Partagé',
            'type_id' => 1,
            'folder_id' => $folder->id,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
            'status' => 'active',
        ]);

        // Share the document
        $this->workplace->documents()->create([
            'document_id' => $document->id,
            'access_level' => 'view',
            'shared_by' => $this->user->id,
            'shared_at' => now(),
        ]);

        $response = $this->getJson(route('workplaces.content.searchDocuments', $this->workplace) . '?q=Déjà Partagé');

        $response->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function test_search_documents_excludes_other_organisation()
    {
        $this->actingAs($this->user);

        $otherOrg = Organisation::create([
            'code' => 'OD' . rand(100, 999),
            'name' => 'Autre Organisation Doc',
        ]);

        $folder = RecordDigitalFolder::create([
            'code' => 'FLD-DOTHER-' . uniqid(),
            'name' => 'Parent Autre',
            'type_id' => 1,
            'organisation_id' => $otherOrg->id,
            'creator_id' => $this->user->id,
            'status' => 'active',
        ]);

        RecordDigitalDocument::create([
            'code' => 'DOC-OTHER-' . uniqid(),
            'name' => 'Document Autre Org',
            'type_id' => 1,
            'folder_id' => $folder->id,
            'organisation_id' => $otherOrg->id,
            'creator_id' => $this->user->id,
            'status' => 'active',
        ]);

        $response = $this->getJson(route('workplaces.content.searchDocuments', $this->workplace) . '?q=Autre Org');

        $response->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function test_search_documents_includes_folder_name()
    {
        $this->actingAs($this->user);

        $folder = RecordDigitalFolder::create([
            'code' => 'FLD-FNAME-' . uniqid(),
            'name' => 'Dossier Comptabilité',
            'type_id' => 1,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
            'status' => 'active',
        ]);

        RecordDigitalDocument::create([
            'code' => 'DOC-FNAME-' . uniqid(),
            'name' => 'Facture Fournisseur Test',
            'type_id' => 1,
            'folder_id' => $folder->id,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
            'status' => 'active',
        ]);

        $response = $this->getJson(route('workplaces.content.searchDocuments', $this->workplace) . '?q=Facture Fournisseur');

        $response->assertStatus(200)
            ->assertJsonFragment(['folder_name' => 'Dossier Comptabilité']);
    }

    public function test_search_documents_returns_expected_fields()
    {
        $this->actingAs($this->user);

        $folder = RecordDigitalFolder::create([
            'code' => 'FLD-DFIELDS-' . uniqid(),
            'name' => 'Parent Fields',
            'type_id' => 1,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
            'status' => 'active',
        ]);

        RecordDigitalDocument::create([
            'code' => 'DOC-FIELDS-' . uniqid(),
            'name' => 'Document Champs Complets',
            'description' => 'Description du document',
            'type_id' => 1,
            'folder_id' => $folder->id,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
            'status' => 'active',
        ]);

        $response = $this->getJson(route('workplaces.content.searchDocuments', $this->workplace) . '?q=Champs Complets');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'text', 'code', 'name', 'description', 'folder_name', 'status']
            ]);
    }

    public function test_search_documents_limits_to_15_results()
    {
        $this->actingAs($this->user);

        $folder = RecordDigitalFolder::create([
            'code' => 'FLD-DLIM-' . uniqid(),
            'name' => 'Parent Limit',
            'type_id' => 1,
            'organisation_id' => $this->organisation->id,
            'creator_id' => $this->user->id,
            'status' => 'active',
        ]);

        for ($i = 1; $i <= 20; $i++) {
            RecordDigitalDocument::create([
                'code' => 'DOC-LIM-' . $i . '-' . uniqid(),
                'name' => 'Document Limite Test ' . $i,
                'type_id' => 1,
                'folder_id' => $folder->id,
                'organisation_id' => $this->organisation->id,
                'creator_id' => $this->user->id,
                'status' => 'active',
            ]);
        }

        $response = $this->getJson(route('workplaces.content.searchDocuments', $this->workplace) . '?q=Limite Test');

        $response->assertStatus(200);
        $this->assertLessThanOrEqual(15, count($response->json()));
    }
}
