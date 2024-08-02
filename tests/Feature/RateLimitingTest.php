<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use JWTAuth;

class RateLimitingTest extends TestCase
{

    public function test_rate_limiting_with_jwt()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $endpoint = '/api/users/' . $user->id;

        // Send 10 requests
        for ($i = 0; $i < 10; $i++) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->getJson($endpoint);

            $response->assertStatus(200)
                     ->assertJsonStructure([
                         'status',
                         'message',
                         'data' => [
                             'id',
                             'first_name',
                             'last_name',
                             'role',
                             'email',
                             'latitude',
                             'longitude',
                             'date_of_birth',
                             'timezone'
                         ]
                     ]);
        }

        // Send 1 more request (should be rate-limited)
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson($endpoint);
        $response->dump();
        $response->assertStatus(429) // 429 Too Many Requests
                 ->assertJson([
                     'status' => 'error',
                     'message' => 'Too many attempts.'
                 ]);
    }
}