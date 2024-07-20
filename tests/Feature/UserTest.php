<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class UserCRUDTest extends TestCase
{

    use RefreshDatabase, WithFaker;

    /**
     *   User should be able to register and receive a success object
     */
    public function test_can_register_user()
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/v1/users', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                "data" => [
                    "user" => [
                        "id" ,
                        "name",
                        "email" ,
                        "created_at",
                        "updated_at" ,
                        "role"
                    ],
                    "access_token",
                    "token_type"
                ],
                "message",
                "status"
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
        ]);
    }

    /**
     *   User should be register with existing email
     */
    public function test_should_not_register_user_with_existing_email()
    {
        $existingUser = User::factory()->create();

        $userData = [
            'name' => $this->faker->name,
            'email' => $existingUser->email,
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/users', $userData);
        $response1 = $this->postJson('/api/v1/users', $userData);

        $response1->assertStatus(422)
            ->assertJson([
                'message' => 'Validation errors',
                'status' => 422
            ])
            ->assertJsonStructure([
                'message',
                'data' => [
                    'email' => []
                ],
                'status'
            ]);

        $this->assertEquals(
            ['The email has already been taken.'],
            $response->json('data.email')
        );

    }

}