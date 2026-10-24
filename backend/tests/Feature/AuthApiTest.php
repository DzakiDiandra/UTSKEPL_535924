<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_berhasil_mengembalikan_token_dan_data_user(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'token',
                'user' => ['name', 'email', 'is_admin'],
            ])
            ->assertJsonPath('user.name', $user->name)
            ->assertJsonPath('user.email', 'admin@example.com')
            ->assertJsonPath('user.is_admin', true);

        $this->assertNotEmpty($response->json('token'));
    }

    public function test_login_gagal_dengan_password_salah(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
        ]);

        $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'password-salah',
        ])
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'Email atau password salah.',
            ]);
    }
}
