<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_register_user()
    {
        $generatedEmail='vignesh.' . uniqid() . '@gmail.com';
        $response = $this->postJson('/api/register', [
            'first_name' => 'Vigneshraj',
            'last_name' => 'R',
            'role' => 'Admin',
            'email' => $generatedEmail,
            'latitude' => '12.34',
            'longitude' => '56.78',
            'date_of_birth' => '1990-04-12',
            'timezone' => 'Asia/Kolkata',
            'password' => '12345678'
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

    public function test_create_user()
    {
        $generatedEmail='vignesh.' . uniqid() . '@gmail.com';
        $user = User::factory()->create();// Create a user
        $token = JWTAuth::fromUser($user);
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
            'password' => '12345678'
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

    public function test_login()
    {
        // Create a user to log in
        $user = User::factory()->create([
            'first_name' => 'Vigneshraj',
            'last_name' => 'R',
            'latitude' => '12.34',
            'longitude' => '56.78',
            'date_of_birth' => '1990-04-12',
            'timezone' => 'Asia/Kolkata',
            'email' => 'vignesh.' . uniqid() . '@gmail.com',
            'password' => Hash::make('12345678'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => '12345678'
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'token'
                     ]
                 ]);

        $responseData = $response->json('data');
        $this->assertNotEmpty($responseData['token']);
    }

    public function test_user_detail_success()
    {
        $user = User::factory()->create();// Create a user
        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users/' . $user->id);

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

    public function test_user_detail_not_found()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users/99999');
        $response->assertStatus(404)
                 ->assertJson([
                     'status' => 'error',
                     'message' => 'User not found !!'
                 ]);
    }

    public function test_update_user_success()
    {
        // Create a user
        $user = User::factory()->create();
        $generatedEmail='update.vignesh.' . uniqid() . '@gmail.com';
        // Send PUT request to update user
        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/users/' . $user->id, [
            'first_name' => 'Vigneshrajj',
            'last_name' => 'R',
            'role' => 'Admin',
            'email' => $generatedEmail,
            'latitude' => 45.678,
            'longitude' => 23.456,
            'date_of_birth' => '1990-05-15',
            'timezone' => 'Europe/London',
        ]);
        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'User updated successfully !!',
                     'data' => [
                         'userId' => $user->id
                     ]
                 ]);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Vigneshrajj',
            'last_name' => 'R',
            'email' => $generatedEmail,
        ]);
    }

    public function test_destroy_user_success()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/users/' . $user->id);
        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'User deleted !!'
                 ]);
        $this->assertSoftDeleted('users', [
            'id' => $user->id
        ]);
    }
}
