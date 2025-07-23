<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\SettingCategory;
use App\Models\User;
use App\Models\Organisation;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SettingMergedStructureTest extends TestCase
{
    use RefreshDatabase;

    private $settingService;
    private $user;
    private $organisation;
    private $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->settingService = app(SettingService::class);

        // Créer les données de test
        $this->category = SettingCategory::create([
            'name' => 'Test Category',
            'description' => 'Category for testing'
        ]);

        $this->user = User::factory()->create();
        $this->organisation = Organisation::factory()->create();
    }

    /** @test */
    public function it_can_create_a_global_setting()
    {
        $setting = Setting::create([
            'category_id' => $this->category->id,
            'name' => 'test_setting',
            'type' => 'string',
            'default_value' => 'default_value',
            'description' => 'Test setting',
            'is_system' => false,
            'constraints' => null,
            'user_id' => null,
            'organisation_id' => null,
            'value' => null
        ]);

        $this->assertDatabaseHas('settings', [
            'name' => 'test_setting',
            'user_id' => null,
            'organisation_id' => null,
            'value' => null
        ]);

        // La valeur effective devrait être la valeur par défaut
        $this->assertEquals('default_value', $setting->getEffectiveValue());
        $this->assertFalse($setting->hasCustomValue());
    }

    /** @test */
    public function it_can_create_a_personalized_setting()
    {
        // Créer d'abord un paramètre global
        $globalSetting = Setting::create([
            'category_id' => $this->category->id,
            'name' => 'test_setting',
            'type' => 'string',
            'default_value' => 'default_value',
            'description' => 'Test setting',
            'is_system' => false,
            'constraints' => null,
            'user_id' => null,
            'organisation_id' => null,
            'value' => null
        ]);

        // Créer un paramètre personnalisé pour l'utilisateur
        $personalizedSetting = Setting::create([
            'category_id' => $this->category->id,
            'name' => 'test_setting',
            'type' => 'string',
            'default_value' => 'default_value',
            'description' => 'Test setting',
            'is_system' => false,
            'constraints' => null,
            'user_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'value' => 'custom_value'
        ]);

        $this->assertDatabaseHas('settings', [
            'name' => 'test_setting',
            'user_id' => $this->user->id,
            'organisation_id' => $this->organisation->id,
            'value' => '"custom_value"'
        ]);

        // La valeur effective devrait être la valeur personnalisée
        $this->assertEquals('custom_value', $personalizedSetting->getEffectiveValue());
        $this->assertTrue($personalizedSetting->hasCustomValue());
    }

    /** @test */
    public function setting_service_returns_correct_values()
    {
        $this->actingAs($this->user);

        // Créer un paramètre global
        Setting::create([
            'category_id' => $this->category->id,
            'name' => 'test_setting',
            'type' => 'string',
            'default_value' => 'default_value',
            'description' => 'Test setting',
            'is_system' => false,
            'constraints' => null,
            'user_id' => null,
            'organisation_id' => null,
            'value' => null
        ]);

        // Avant personnalisation, devrait retourner la valeur par défaut
        $value = $this->settingService->get('test_setting');
        $this->assertEquals('default_value', $value);

        // Après personnalisation, devrait retourner la valeur personnalisée
        $this->settingService->set('test_setting', 'custom_value');
        $value = $this->settingService->get('test_setting');
        $this->assertEquals('custom_value', $value);

        // Après réinitialisation, devrait retourner la valeur par défaut
        $this->settingService->reset('test_setting');
        $value = $this->settingService->get('test_setting');
        $this->assertEquals('default_value', $value);
    }

    /** @test */
    public function scope_for_user_and_organisation_works_correctly()
    {
        // Créer différents types de paramètres
        $globalSetting = Setting::create([
            'category_id' => $this->category->id,
            'name' => 'global_setting',
            'type' => 'string',
            'default_value' => 'global_value',
            'description' => 'Global setting',
            'is_system' => false,
            'user_id' => null,
            'organisation_id' => null,
            'value' => null
        ]);

        $userSetting = Setting::create([
            'category_id' => $this->category->id,
            'name' => 'user_setting',
            'type' => 'string',
            'default_value' => 'default_value',
            'description' => 'User setting',
            'is_system' => false,
            'user_id' => $this->user->id,
            'organisation_id' => null,
            'value' => 'user_value'
        ]);

        $orgSetting = Setting::create([
            'category_id' => $this->category->id,
            'name' => 'org_setting',
            'type' => 'string',
            'default_value' => 'default_value',
            'description' => 'Organisation setting',
            'is_system' => false,
            'user_id' => null,
            'organisation_id' => $this->organisation->id,
            'value' => 'org_value'
        ]);

        // Tester le scope
        $settings = Setting::forUserAndOrganisation($this->user->id, $this->organisation->id)->get();

        $this->assertGreaterThanOrEqual(3, $settings->count());
        $this->assertTrue($settings->contains('name', 'global_setting'));
        $this->assertTrue($settings->contains('name', 'user_setting'));
        $this->assertTrue($settings->contains('name', 'org_setting'));
    }
}
