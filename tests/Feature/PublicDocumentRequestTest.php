<?php

namespace Tests\Feature;

use App\Models\PublicUser;
use App\Models\PublicRecord;
use App\Models\PublicDocumentRequest;
use App\Models\Record;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicDocumentRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_user_can_create_document_request(): void
    {
        $user = PublicUser::factory()->approved()->create();
        $internalUser = User::factory()->create();
        $record = Record::factory()->create();
        $publicRecord = PublicRecord::factory()->create([
            'record_id' => $record->id,
            'published_by' => $internalUser->id,
        ]);

        $requestData = [
            'record_id' => $publicRecord->id,
            'request_type' => 'digital',
            'reason' => 'Pour mes recherches personnelles',
        ];

        $response = $this->actingAs($user, 'sanctum')
                         ->post(route('api.public.documents.request'), $requestData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('public_document_requests', [
            'user_id' => $user->id,
            'record_id' => $publicRecord->id,
            'request_type' => 'digital',
            'status' => 'pending',
        ]);
    }

    public function test_pending_user_cannot_create_document_request(): void
    {
        $user = PublicUser::factory()->pending()->create();
        $internalUser = User::factory()->create();
        $record = Record::factory()->create();
        $publicRecord = PublicRecord::factory()->create([
            'record_id' => $record->id,
            'published_by' => $internalUser->id,
        ]);

        $requestData = [
            'record_id' => $publicRecord->id,
            'request_type' => 'digital',
            'reason' => 'Pour mes recherches',
        ];

        $response = $this->actingAs($user, 'sanctum')
                         ->post(route('api.public.documents.request'), $requestData);

        $response->assertStatus(403);
    }

    public function test_user_can_view_their_document_requests(): void
    {
        $user = PublicUser::factory()->approved()->create();
        $internalUser = User::factory()->create();
        $record = Record::factory()->create();
        $publicRecord = PublicRecord::factory()->create([
            'record_id' => $record->id,
            'published_by' => $internalUser->id,
        ]);

        $documentRequest = PublicDocumentRequest::factory()->create([
            'user_id' => $user->id,
            'record_id' => $publicRecord->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
                         ->get(route('api.public.documents.requests'));

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'id' => $documentRequest->id,
                     'status' => $documentRequest->status,
                 ]);
    }

    public function test_user_cannot_view_others_document_requests(): void
    {
        $user1 = PublicUser::factory()->approved()->create();
        $user2 = PublicUser::factory()->approved()->create();
        $internalUser = User::factory()->create();
        $record = Record::factory()->create();
        $publicRecord = PublicRecord::factory()->create([
            'record_id' => $record->id,
            'published_by' => $internalUser->id,
        ]);

        $documentRequest = PublicDocumentRequest::factory()->create([
            'user_id' => $user2->id,
            'record_id' => $publicRecord->id,
        ]);

        $response = $this->actingAs($user1, 'sanctum')
                         ->get(route('api.public.documents.requests.show', $documentRequest));

        $response->assertStatus(403);
    }

    public function test_user_can_cancel_pending_request(): void
    {
        $user = PublicUser::factory()->approved()->create();
        $internalUser = User::factory()->create();
        $record = Record::factory()->create();
        $publicRecord = PublicRecord::factory()->create([
            'record_id' => $record->id,
            'published_by' => $internalUser->id,
        ]);

        $documentRequest = PublicDocumentRequest::factory()->pending()->create([
            'user_id' => $user->id,
            'record_id' => $publicRecord->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
                         ->delete(route('public.document-requests.destroy', $documentRequest));

        $response->assertRedirect();
        $this->assertSoftDeleted('public_document_requests', [
            'id' => $documentRequest->id,
        ]);
    }

    public function test_user_cannot_cancel_approved_request(): void
    {
        $user = PublicUser::factory()->approved()->create();
        $internalUser = User::factory()->create();
        $record = Record::factory()->create();
        $publicRecord = PublicRecord::factory()->create([
            'record_id' => $record->id,
            'published_by' => $internalUser->id,
        ]);

        $documentRequest = PublicDocumentRequest::factory()->approved()->create([
            'user_id' => $user->id,
            'record_id' => $publicRecord->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
                         ->delete(route('public.document-requests.destroy', $documentRequest));

        $response->assertStatus(403);
        $this->assertDatabaseHas('public_document_requests', [
            'id' => $documentRequest->id,
            'deleted_at' => null,
        ]);
    }
}
