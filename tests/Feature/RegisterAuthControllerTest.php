<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegisterAuthControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
        // use RefreshDatabase;   using this will reload all the data in d database and clear them
        use DatabaseTransactions;  //........... this will manully maintain the information on d database and still run the testing

    // .............testing registration start from hwere...............

    public function test_register_user_success()
{
    $userData = [
        'name' => 'Williams Olaniyi',
        'email' => 'ola.Williams@example.com',
        'password' => 'password123',
    ];

    $response = $this->postJson('/api/register', $userData);

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'status',
                 'message',
                 'user' => [
                     'id',
                     'name',
                     'email',
                     'created_at',
                     'updated_at',
                 ],
                 'authorisation' => [
                     'token',
                     'type',
                 ]
             ]);
   }


    public function test_register_user_email_exists()
    {
        // Create a user with the same email to simulate email conflict
        User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com', // Existing email
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(409)
                 ->assertJson([
                     'status' => 'error',
                     'message' => 'Email already exists',
                 ]);
    }

    public function test_register_user_validation_errors()
    {
        $userData = [
            // Missing email field intentionally you get error when testing this
            'name' => 'Test User',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'message',
                     'errors' => [
                         'email',
                     ],
                 ]);
    }

}
