<?php

namespace Tests\Feature;

use App\Models\PublicUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PublicUserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_public_user_can_register(): void
    {
        $userData = [
            'name' => 'Dupont',
            'first_name' => 'Jean',
            'phone1' => '0123456789',
            'phone2' => '0987654321',
            'address' => '1 rue de la Paix, 75001 Paris',
            'email' => 'jean.dupont@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post(route('public.users.store'), $userData);

        $response->assertRedirect();
        $this->assertDatabaseHas('public_users', [
            'email' => 'jean.dupont@example.com',
            'name' => 'Dupont',
            'first_name' => 'Jean',
        ]);
    }    public function test_approved_user_can_login(): void
    {
        PublicUser::factory()->approved()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post(route('api.public.users.login'), [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'user',
                    'token',
                    'expires_at',
                ]);
    }

    public function test_pending_user_cannot_login(): void
    {
        PublicUser::factory()->pending()->create([
            'email' => 'pending@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post(route('api.public.users.login'), [
            'email' => 'pending@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(403)
                ->assertJson([
                    'message' => 'Votre compte est en attente d\'approbation.',
                ]);
    }

    public function test_user_can_view_their_profile(): void
    {
        $user = PublicUser::factory()->approved()->create();

        $response = $this->actingAs($user, 'sanctum')
                         ->get(route('public.users.show', $user));

        $response->assertStatus(200)
                 ->assertViewIs('public.users.show')
                 ->assertViewHas('user', $user);
    }

    public function test_user_can_update_their_profile(): void
    {
        $user = PublicUser::factory()->approved()->create();

        $updateData = [
            'name' => 'NouveauNom',
            'first_name' => 'NouveauPrenom',
            'phone1' => '0111111111',
            'address' => 'Nouvelle adresse',
        ];

        $response = $this->actingAs($user, 'sanctum')
                         ->patch(route('api.public.users.update-profile'), $updateData);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertEquals('NouveauNom', $user->name);
        $this->assertEquals('NouveauPrenom', $user->first_name);
    }

    public function test_registration_validation_works(): void
    {
        $response = $this->post(route('public.users.store'), [
            'email' => 'invalid-email',
            'password' => '123', // Trop court
        ]);

        $response->assertSessionHasErrors(['email', 'password', 'name', 'first_name']);
    }

    public function test_unique_email_validation(): void
    {
        PublicUser::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post(route('public.users.store'), [
            'name' => 'Test',
            'first_name' => 'User',
            'phone1' => '0123456789',
            'address' => 'Test address',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_user_can_logout(): void
    {
        $user = PublicUser::factory()->approved()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->post(route('api.public.users.logout'));

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Déconnexion réussie']);
    }
}
