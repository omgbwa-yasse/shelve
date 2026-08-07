<?php

namespace Tests\Feature;

use App\Exports\ReferenceValueExport;
use App\Imports\ReferenceValueImport;
use App\Models\ReferenceList;
use App\Models\ReferenceValue;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/**
 * Étape 7 — Import / Export en masse des valeurs d'un domaine.
 */
class ReferenceValueImportExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected ReferenceList $list;
    protected static int $counter = 0;

    protected function setUp(): void
    {
        parent::setUp();
        self::$counter++;

        $this->user = User::create([
            'name' => 'Import ' . self::$counter,
            'email' => 'import-' . self::$counter . '@example.com',
            'password' => bcrypt('password'),
            'birthday' => '1990-01-01',
        ]);
        $this->user->roles()->attach(Role::firstOrCreate(['name' => 'superadmin'])->id);

        $this->list = ReferenceList::create([
            'name' => 'Domaine import ' . self::$counter,
            'code' => 'IMPORT-' . self::$counter,
            'created_by' => $this->user->id,
        ]);
    }

    public function test_export_contains_expected_headings(): void
    {
        ReferenceValue::create(['list_id' => $this->list->id, 'value' => 'Alpha', 'code' => 'ALPHA', 'created_by' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('settings.reference-lists.values.export', $this->list));

        $response->assertOk();
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition') ?: '');
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition') ?: '');
    }

    public function test_import_creates_and_updates_values(): void
    {
        $file = Excel::store(
            new ReferenceValueExport($this->list),
            'test-domaine-' . self::$counter . '.xlsx',
            'local'
        );

        $this->assertTrue($file);

        $import = new ReferenceValueImport($this->list, $this->user->id);
        Excel::import($import, 'test-domaine-' . self::$counter . '.xlsx', 'local');

        $summary = $import->getSummary();

        // L'export ne contient aucune valeur → aucune création.
        $this->assertEquals(0, $summary['created']);
        $this->assertEquals(0, $summary['updated']);
    }

    public function test_import_reports_invalid_lines(): void
    {
        ReferenceValue::create(['list_id' => $this->list->id, 'value' => 'Existant', 'code' => 'EXIST', 'created_by' => $this->user->id]);

        $import = new ReferenceValueImport($this->list, $this->user->id);
        $import->collection(collect([
            ['code' => '', 'value' => 'Sans code', 'description' => null, 'active' => 'oui'],
            ['code' => 'EXIST', 'value' => 'Mis à jour', 'description' => null, 'active' => 'oui'],
            ['code' => 'NEW1', 'value' => 'Nouvelle', 'description' => null, 'active' => 'oui'],
        ]));

        $summary = $import->getSummary();

        $this->assertEquals(1, $summary['created']);
        $this->assertEquals(1, $summary['updated']);
        $this->assertCount(1, $summary['errors']);
        $this->assertStringContainsString('Ligne 2', $summary['errors'][0]);
    }

    public function test_import_via_route(): void
    {
        ReferenceValue::create(['list_id' => $this->list->id, 'value' => 'Existant', 'code' => 'EXIST', 'created_by' => $this->user->id]);

        $path = 'test-route-' . self::$counter . '.xlsx';
        Excel::store(new ReferenceValueExport($this->list), $path, 'local');

        $file = new \Illuminate\Http\UploadedFile(
            storage_path('app/' . $path),
            'import.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->actingAs($this->user)->post(route('settings.reference-lists.values.import', $this->list), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('settings.reference-lists.show', $this->list));
        $response->assertSessionHas('success');
    }
}
