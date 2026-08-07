<?php

namespace Tests\Feature\Feature;

use Tests\TestCase;
use App\Models\Template;
use App\Services\OPAC\OpacConfigurationService;
use App\Services\OPAC\TemplateEngineService;
use App\Services\OPAC\ThemeManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class OpacTemplateSystemTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function services_are_properly_registered()
    {
        $configService = app(OpacConfigurationService::class);
        $templateEngine = app(TemplateEngineService::class);
        $themeManager = app(ThemeManagerService::class);

        $this->assertInstanceOf(OpacConfigurationService::class, $configService);
        $this->assertInstanceOf(TemplateEngineService::class, $templateEngine);
        $this->assertInstanceOf(ThemeManagerService::class, $themeManager);
    }

    /** @test */
    public function seeded_templates_are_created_correctly()
    {
        $this->artisan('db:seed', ['--class' => 'OpacTemplateSeeder']);

        $this->assertDatabaseHas('templates', [
            'slug' => 'modern-academic',
            'status' => 'active',
            'is_default' => true
        ]);

        $this->assertDatabaseHas('templates', [
            'slug' => 'classic-library',
            'status' => 'active'
        ]);

        $modernTemplate = Template::where('slug', 'modern-academic')->first();
        $this->assertNotNull($modernTemplate->layout);
        $this->assertNotNull($modernTemplate->custom_css);
        $this->assertIsArray($modernTemplate->variables);
    }

    /** @test */
    public function template_model_works_correctly()
    {
        $template = Template::create([
            'name' => 'Test Template',
            'slug' => 'test-template',
            'type' => 'opac',
            'status' => 'active',
            'layout' => '<div>Test Layout</div>',
            'variables' => ['color' => '#000000'],
            'created_by' => 'test-user'
        ]);

        $this->assertDatabaseHas('templates', [
            'slug' => 'test-template',
            'name' => 'Test Template'
        ]);

        $this->assertEquals('test-template', $template->slug);
        $this->assertTrue($template->is_editable);
    }
}
