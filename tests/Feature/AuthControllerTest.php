<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;

class AuthControllerTest extends TestCase
{
    // use RefreshDatabase;   using this will reload all the data in d database and clear them
    use DatabaseTransactions;  //........... this will manully maintain the information on d database and still run the testing

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);
    }

    /** @test */
    public function login_with_valid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'user',
                     'authorisation' => [
                         'token',
                         'type',
                     ],
                 ]);
    }

    /** @test */
    public function login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'status' => 'error',
                     'message' => 'Unauthorized',
                 ]);
    }

    /** @test */
    public function login_with_missing_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'message',
                     'errors' => [
                         'email',
                         'password',
                     ],
                 ]);
    }



}
