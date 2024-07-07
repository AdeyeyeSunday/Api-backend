<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $cacheTime = 3600; // Cache time set to one hour
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');

        // Create a test user
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_fetches_all_posts()
    {
        Post::factory(5)->create();

        $response = $this->actingAs($this->user, 'api')->getJson('/api/user/all-posts');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'data' => [
                         '*' => [
                             'id', 'user_id', 'post_title', 'content', 'created_at', 'updated_at'
                         ]
                     ],
                 ]);
    }

    /** @test */
    public function it_fetches_a_single_post_by_id()
    {
        $post = Post::factory()->create();

        $response = $this->actingAs($this->user, 'api')->getJson("/api/user/get-post/{$post->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Post fetched successfully',
                     'data' => [
                         'id' => $post->id,
                         'post_title' => $post->post_title,
                         'content' => $post->content,
                     ]
                 ]);
    }

    /** @test */
    public function it_fetches_all_posts_of_a_user()
    {
        Post::factory(5)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'api')->getJson('/api/user/get-all-user-posts');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'data' => [
                         '*' => [
                             'id', 'user_id', 'post_title', 'content', 'created_at', 'updated_at'
                         ]
                     ],
                 ]);
    }

    /** @test */
    public function it_creates_a_new_post()
    {
        $postData = [
            'post_title' => 'Test Post',
            'content' => 'This is a test post content.',
        ];

        $response = $this->actingAs($this->user, 'api')->postJson('/api/user/create-post', $postData);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Post created successfully',
                     'data' => [
                         'post_title' => $postData['post_title'],
                         'content' => $postData['content'],
                     ]
                 ]);

        $this->assertDatabaseHas('posts', $postData);
    }

    /** @test */
    public function it_updates_an_existing_post()
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'post_title' => 'Updated Post Title',
            'content' => 'Updated post content.',
        ];

        $response = $this->actingAs($this->user, 'api')->patchJson("/api/user/update-post/{$post->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Post updated successfully',
                     'post' => [
                         'post_title' => $updateData['post_title'],
                         'content' => $updateData['content'],
                     ]
                 ]);

        $this->assertDatabaseHas('posts', $updateData);
    }

    /** @test */
    public function it_deletes_an_existing_post()
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'api')->deleteJson("/api/user/delete-post/{$post->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Post deleted successfully',
                 ]);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    /** @test */
    public function it_creates_a_comment_on_a_post()
    {
        $post = Post::factory()->create();

        $commentData = [
            'post_id' => $post->id,
            'comment' => 'This is a test comment.',
        ];

        $response = $this->actingAs($this->user, 'api')->postJson("/api/user/comment/{$post->id}", $commentData);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Comment created successfully',
                     'data' => [
                         'comment' => $commentData['comment'],
                     ]
                 ]);

        $this->assertDatabaseHas('comments', $commentData);
    }
}
