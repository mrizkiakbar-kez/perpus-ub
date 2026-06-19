<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('member.dashboard', absolute: false));

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'member',
        ]);

        $this->assertDatabaseHas('members', [
            'email' => 'test@example.com',
            'role' => 'member',
            'nama' => 'Test User',
        ]);
    }

    public function test_registration_does_not_conflict_with_existing_member_codes(): void
    {
        // 1. Manually insert a member with kode_anggota MBR001
        \App\Models\Member::create([
            'kode_anggota' => 'MBR001',
            'nama' => 'Existing Member 1',
            'email' => 'existing1@example.com',
            'password' => 'password',
            'telepon' => '-',
            'alamat' => '-',
            'role' => 'member',
        ]);

        // 2. Manually insert a member with kode_anggota MBR002
        \App\Models\Member::create([
            'kode_anggota' => 'MBR002',
            'nama' => 'Existing Member 2',
            'email' => 'existing2@example.com',
            'password' => 'password',
            'telepon' => '-',
            'alamat' => '-',
            'role' => 'member',
        ]);

        // 3. Register a new user
        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('member.dashboard', absolute: false));

        // 4. Assert new user was registered with kode_anggota MBR003
        $this->assertDatabaseHas('members', [
            'email' => 'new@example.com',
            'kode_anggota' => 'MBR003',
        ]);
    }
}
