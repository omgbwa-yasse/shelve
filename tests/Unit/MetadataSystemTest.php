<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\RecordDigitalDocument;
use App\Models\RecordDigitalDocumentType;
use App\Models\MetadataDefinition;
use App\Models\ReferenceList;
use App\Models\ReferenceValue;
use App\Services\MetadataValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

class MetadataSystemTest extends TestCase
{
    use RefreshDatabase;

    protected $documentType;
    protected $metadataService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->metadataService = app(MetadataValidationService::class);

        // Créer un type de document
        $this->documentType = RecordDigitalDocumentType::create([
            'code' => 'test_doc',
            'name' => 'Document de test',
            'description' => 'Type de document pour les tests',
        ]);
    }

    /** @test */
    public function it_can_create_metadata_definition()
    {
        $definition = MetadataDefinition::create([
            'code' => 'test_field',
            'name' => 'Champ de test',
            'data_type' => 'text',
            'description' => 'Un champ de test',
            'searchable' => true,
            'active' => true,
        ]);

        $this->assertDatabaseHas('metadata_definitions', [
            'code' => 'test_field',
            'data_type' => 'text',
        ]);
    }

    /** @test */
    public function it_can_create_reference_list_with_values()
    {
        $list = ReferenceList::create([
            'code' => 'test_list',
            'name' => 'Liste de test',
        ]);

        $value = ReferenceValue::create([
            'reference_list_id' => $list->id,
            'code' => 'value1',
            'label' => 'Valeur 1',
            'sort_order' => 1,
        ]);

        $this->assertCount(1, $list->values);
        $this->assertEquals('Valeur 1', $list->values->first()->label);
    }

    /** @test */
    public function it_can_attach_metadata_to_document_type()
    {
        $definition = MetadataDefinition::create([
            'code' => 'isbn',
            'name' => 'ISBN',
            'data_type' => 'text',
        ]);

        $this->documentType->metadataDefinitions()->attach($definition->id, [
            'mandatory' => true,
            'visible' => true,
            'readonly' => false,
            'sort_order' => 1,
        ]);

        $this->assertTrue($this->documentType->metadataDefinitions->contains($definition));
    }

    /** @test */
    public function it_validates_mandatory_metadata()
    {
        $definition = MetadataDefinition::create([
            'code' => 'isbn',
            'name' => 'ISBN',
            'data_type' => 'text',
        ]);

        $this->documentType->metadataDefinitions()->attach($definition->id, [
            'mandatory' => true,
            'visible' => true,
        ]);

        $this->expectException(ValidationException::class);

        // Validation devrait échouer car le champ obligatoire est manquant
        $this->metadataService->validateDocumentMetadata($this->documentType->id, []);
    }

    /** @test */
    public function it_validates_metadata_data_types()
    {
        $definition = MetadataDefinition::create([
            'code' => 'pages',
            'name' => 'Nombre de pages',
            'data_type' => 'number',
        ]);

        $this->documentType->metadataDefinitions()->attach($definition->id, [
            'mandatory' => true,
            'visible' => true,
        ]);

        $this->expectException(ValidationException::class);

        // Validation devrait échouer car "abc" n'est pas un nombre
        $this->metadataService->validateDocumentMetadata($this->documentType->id, [
            'pages' => 'abc',
        ]);
    }

    /** @test */
    public function it_can_set_and_get_document_metadata()
    {
        $document = RecordDigitalDocument::create([
            'code' => 'DOC-001',
            'name' => 'Test Document',
            'type_id' => $this->documentType->id,
            'creator_id' => 1,
        ]);

        $document->setMetadataValue('isbn', '978-2-07-036864-1');
        $document->save();

        $this->assertEquals('978-2-07-036864-1', $document->getMetadataValue('isbn'));
    }

    /** @test */
    public function it_can_set_multiple_metadata_at_once()
    {
        $document = RecordDigitalDocument::create([
            'code' => 'DOC-001',
            'name' => 'Test Document',
            'type_id' => $this->documentType->id,
            'creator_id' => 1,
        ]);

        $document->setMultipleMetadata([
            'isbn' => '978-2-07-036864-1',
            'auteur' => 'Victor Hugo',
            'pages' => 1900,
        ]);
        $document->save();

        $this->assertEquals('978-2-07-036864-1', $document->getMetadataValue('isbn'));
        $this->assertEquals('Victor Hugo', $document->getMetadataValue('auteur'));
        $this->assertEquals(1900, $document->getMetadataValue('pages'));
    }

    /** @test */
    public function it_returns_required_metadata_fields()
    {
        $definition1 = MetadataDefinition::create([
            'code' => 'isbn',
            'name' => 'ISBN',
            'data_type' => 'text',
        ]);

        $definition2 = MetadataDefinition::create([
            'code' => 'auteur',
            'name' => 'Auteur',
            'data_type' => 'text',
        ]);

        $this->documentType->metadataDefinitions()->attach([
            $definition1->id => ['mandatory' => true, 'visible' => true],
            $definition2->id => ['mandatory' => false, 'visible' => true],
        ]);

        $document = RecordDigitalDocument::create([
            'code' => 'DOC-001',
            'name' => 'Test Document',
            'type_id' => $this->documentType->id,
            'creator_id' => 1,
        ]);

        $requiredFields = $document->getRequiredMetadataFields();

        $this->assertCount(1, $requiredFields);
        $this->assertEquals('isbn', $requiredFields[0]['code']);
    }

    /** @test */
    public function it_applies_default_values()
    {
        $definition = MetadataDefinition::create([
            'code' => 'langue',
            'name' => 'Langue',
            'data_type' => 'select',
            'options' => json_encode(['francais', 'anglais']),
        ]);

        $this->documentType->metadataDefinitions()->attach($definition->id, [
            'mandatory' => false,
            'visible' => true,
            'default_value' => 'francais',
        ]);

        $fields = $this->metadataService->getDocumentMetadataFields($this->documentType->id);
        $metadata = $this->metadataService->applyDefaultValues([], $fields);

        $this->assertEquals('francais', $metadata['langue']);
    }

    /** @test */
    public function it_validates_select_field_options()
    {
        $definition = MetadataDefinition::create([
            'code' => 'langue',
            'name' => 'Langue',
            'data_type' => 'select',
            'options' => json_encode(['francais', 'anglais', 'espagnol']),
        ]);

        $this->documentType->metadataDefinitions()->attach($definition->id, [
            'mandatory' => true,
            'visible' => true,
        ]);

        $this->expectException(ValidationException::class);

        // Validation devrait échouer car "allemand" n'est pas dans les options
        $this->metadataService->validateDocumentMetadata($this->documentType->id, [
            'langue' => 'allemand',
        ]);
    }

    /** @test */
    public function it_validates_reference_list_values()
    {
        $list = ReferenceList::create([
            'code' => 'editeurs',
            'name' => 'Éditeurs',
        ]);

        ReferenceValue::create([
            'reference_list_id' => $list->id,
            'code' => 'gallimard',
            'label' => 'Gallimard',
            'active' => true,
        ]);

        $definition = MetadataDefinition::create([
            'code' => 'editeur',
            'name' => 'Éditeur',
            'data_type' => 'reference_list',
            'reference_list_id' => $list->id,
        ]);

        $this->documentType->metadataDefinitions()->attach($definition->id, [
            'mandatory' => true,
            'visible' => true,
        ]);

        $this->expectException(ValidationException::class);

        // Validation devrait échouer car l'ID n'existe pas
        $this->metadataService->validateDocumentMetadata($this->documentType->id, [
            'editeur' => 99999,
        ]);
    }

    /** @test */
    public function it_validates_email_field()
    {
        $definition = MetadataDefinition::create([
            'code' => 'contact_email',
            'name' => 'Email de contact',
            'data_type' => 'email',
        ]);

        $this->documentType->metadataDefinitions()->attach($definition->id, [
            'mandatory' => true,
            'visible' => true,
        ]);

        $this->expectException(ValidationException::class);

        // Validation devrait échouer car "invalid" n'est pas un email valide
        $this->metadataService->validateDocumentMetadata($this->documentType->id, [
            'contact_email' => 'invalid',
        ]);
    }

    /** @test */
    public function it_validates_url_field()
    {
        $definition = MetadataDefinition::create([
            'code' => 'website',
            'name' => 'Site web',
            'data_type' => 'url',
        ]);

        $this->documentType->metadataDefinitions()->attach($definition->id, [
            'mandatory' => true,
            'visible' => true,
        ]);

        $this->expectException(ValidationException::class);

        // Validation devrait échouer car "not-a-url" n'est pas une URL valide
        $this->metadataService->validateDocumentMetadata($this->documentType->id, [
            'website' => 'not-a-url',
        ]);
    }

    /** @test */
    public function it_can_check_if_metadata_is_complete()
    {
        $definition = MetadataDefinition::create([
            'code' => 'isbn',
            'name' => 'ISBN',
            'data_type' => 'text',
        ]);

        $this->documentType->metadataDefinitions()->attach($definition->id, [
            'mandatory' => true,
            'visible' => true,
        ]);

        $document = RecordDigitalDocument::create([
            'code' => 'DOC-001',
            'name' => 'Test Document',
            'type_id' => $this->documentType->id,
            'creator_id' => 1,
        ]);

        // Sans métadonnées, devrait être incomplet
        $this->assertFalse($document->hasCompleteMetadata());

        // Avec les métadonnées requises, devrait être complet
        $document->setMetadataValue('isbn', '978-2-07-036864-1');
        $document->save();

        $this->assertTrue($document->hasCompleteMetadata());
    }

    /** @test */
    public function helper_functions_work_correctly()
    {
        $definition = MetadataDefinition::create([
            'code' => 'test_field',
            'name' => 'Test Field',
            'data_type' => 'text',
        ]);

        $this->documentType->metadataDefinitions()->attach($definition->id, [
            'mandatory' => true,
            'visible' => true,
        ]);

        // Test get_document_metadata_fields helper
        $fields = get_document_metadata_fields($this->documentType->id);
        $this->assertIsArray($fields);
        $this->assertArrayHasKey('test_field', $fields);

        // Test get_metadata_data_types helper
        $dataTypes = get_metadata_data_types();
        $this->assertIsArray($dataTypes);
        $this->assertArrayHasKey('text', $dataTypes);

        // Test format_metadata_value helper
        $formatted = format_metadata_value(true, 'boolean');
        $this->assertEquals('Oui', $formatted);

        $formatted = format_metadata_value(false, 'boolean');
        $this->assertEquals('Non', $formatted);
    }
}
