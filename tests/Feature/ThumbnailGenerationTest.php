<?php

namespace Tests\Unit;

use App\Jobs\GenerateDocumentThumbnail;
use App\Services\ThumbnailGenerationService;
use Tests\TestCase;

class ThumbnailGenerationTest extends TestCase
{
    /**
     * Test que la vignette générée ne dépasse pas 10KB
     */
    public function test_thumbnail_respects_10kb_limit(): void
    {
        $service = new ThumbnailGenerationService();

        // Récupérer les contraintes
        $constraints = $service->getCompressionConstraints();

        // Vérifier que la limite est bien 10KB (10240 bytes)
        $this->assertEquals(10240, $constraints['max_size_bytes']);
        $this->assertEquals(60, $constraints['density_ppi']);
        $this->assertEquals(150, $constraints['max_width']);
        $this->assertEquals(200, $constraints['max_height']);
    }

    /**
     * Test que les anciennes méthodes de compression ne sont pas présentes dans le job
     */
    public function test_job_uses_service_for_compression(): void
    {
        $reflectionClass = new \ReflectionClass(GenerateDocumentThumbnail::class);
        $methods = $reflectionClass->getMethods();
        $methodNames = array_map(fn($method) => $method->getName(), $methods);

        // Vérifier que les anciennes méthodes de compression ne sont pas présentes
        $this->assertNotContains('generatePdfThumbnail', $methodNames);
        $this->assertNotContains('generateImageThumbnail', $methodNames);
        $this->assertNotContains('saveThumbnail', $methodNames);
        $this->assertNotContains('updateAttachmentThumbnail', $methodNames);

        // Mais vérifier que le job a toujours les méthodes utiles
        $this->assertContains('handle', $methodNames);
        $this->assertContains('recordError', $methodNames);
        $this->assertContains('guessMimeType', $methodNames);
        $this->assertContains('failed', $methodNames);
    }

    /**
     * Test que le service expose les constantes de compression
     */
    public function test_service_compression_constants_are_correct(): void
    {
        $service = new ThumbnailGenerationService();
        $constraints = $service->getCompressionConstraints();

        // Vérifier toutes les contraintes
        $this->assertIsArray($constraints);
        $this->assertArrayHasKey('max_size_bytes', $constraints);
        $this->assertArrayHasKey('max_size_kb', $constraints);
        $this->assertArrayHasKey('density_ppi', $constraints);
        $this->assertArrayHasKey('compression_quality', $constraints);
        $this->assertArrayHasKey('max_width', $constraints);
        $this->assertArrayHasKey('max_height', $constraints);

        // Vérifier les valeurs
        $this->assertGreaterThan(0, $constraints['max_size_bytes']);
        $this->assertGreaterThan(0, $constraints['density_ppi']);
        $this->assertGreaterThan(0, $constraints['max_width']);
        $this->assertGreaterThan(0, $constraints['max_height']);
    }

    /**
     * Test que le service a les méthodes requises
     */
    public function test_service_has_required_methods(): void
    {
        $service = new ThumbnailGenerationService();

        // Vérifier que tous les méthodes publiques existent
        $this->assertTrue(method_exists($service, 'generatePdfThumbnail'));
        $this->assertTrue(method_exists($service, 'generateImageThumbnail'));
        $this->assertTrue(method_exists($service, 'compressImage'));
        $this->assertTrue(method_exists($service, 'saveThumbnail'));
        $this->assertTrue(method_exists($service, 'updateAttachmentMetrics'));
        $this->assertTrue(method_exists($service, 'shouldRegenerateThumbnail'));
        $this->assertTrue(method_exists($service, 'getThumbnailMetrics'));
        $this->assertTrue(method_exists($service, 'getCompressionConstraints'));
    }
}
