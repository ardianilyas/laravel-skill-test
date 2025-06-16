<?php

namespace Tests\Feature;

use App\Models\Post;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_it_can_store_post_successfully()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $data = [
            'user_id' => $user->id,
            'title' => "Post Title",
            'content' => "Post content",
            'is_draft' => false,
            'published_at' => null,
        ];

        $response = $this->postJson('/api/posts', $data);

        $response->assertCreated()->assertJsonFragment(['title' => 'Post Title']);
    }

    public function test_it_fails_to_store_post_with_invalid_data() 
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $data = [
            'title' => '',
            'content' => '',
            'is_draft' => 'not-a-boolean',
            'published_at' => 'not-a-date',
        ];

        $response = $this->postJson('/api/posts', $data);
        $response->assertStatus(422)->assertJsonValidationErrors(['title', 'content', 'is_draft', 'published_at']);
    }

    public function test_it_can_update_post_when_authorized() 
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'sanctum');

        $response = $this->putJson("/api/posts/{$post->id}", [
            'title' => 'Updated Title',
            'content' => 'Updated Content',
        ]);

        $response->assertOk()->assertJsonFragment(['title' => 'Updated Title']);
    }

    public function test_it_fails_to_update_post_when_not_authorized()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->for($otherUser)->create();

        $this->actingAs($user, 'sanctum');

        $response = $this->putJson("/api/posts/{$post->id}", [
            'title' => 'Unauthorized update',
            'content' => 'Unauthorized content',
        ]);

        $response->assertStatus(403)->assertJson(['message' => 'You are not authorized to update this post.']);
    }

    public function test_it_can_delete_post_when_authorized()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'sanctum');

        $response = $this->deleteJson("/api/posts/{$post->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_it_fails_to_delete_post_when_not_authorized()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->for($otherUser)->create();

        $this->actingAs($user, 'sanctum');

        $response = $this->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(403)->assertJson(['message' => 'You are not authorized to delete this post.']);
    }
}
