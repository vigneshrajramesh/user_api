<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use JWTAuth;

class CheckContentLengthTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_request_within_content_length_limit()
    {
        // 512 KB
        $payload = str_repeat('a', 512 * 1024);
        $generatedEmail='vignesh.' . uniqid() . '@gmail.com';
        $response = $this->postJson('/api/users', [
            'first_name' => 'Vigneshraj',
            'last_name' => 'R',
            'role' => 'Admin',
            'email' => $generatedEmail,
            'latitude' => '12.34',
            'longitude' => '56.78',
            'date_of_birth' => '1990-04-12',
            'timezone' => 'Asia/Kolkata',
            'password' => '12345678',
            'payload' => $payload
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'userId',
                         'token'
                     ]
                 ]);

        $this->assertDatabaseHas('users', [
            'email' => $generatedEmail,
        ]);
    }
    public function test_request_exceeding_content_length_limit()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        // 2 MB
        $payload = str_repeat('a', 2 * 1024 * 1024);
        $generatedEmail='vignesh.' . uniqid() . '@gmail.com';
         $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->postJson('/api/users', [
            'first_name' => 'Vigneshraj',
            'last_name' => 'R',
            'role' => 'Admin',
            'email' => $generatedEmail,
            'latitude' => '12.34',
            'longitude' => '56.78',
            'date_of_birth' => '1990-04-12',
            'timezone' => 'Asia/Kolkata',
            'password' => '12345678',
            'payload' => $payload
        ]);

        $response->assertStatus(413) //413 Payload Too Large respoinse
                 ->assertJson([
                     'status' => 'error',
                     'message' => 'Content length exceeds the maximum allowed limit.'
                 ]);
    }
}
