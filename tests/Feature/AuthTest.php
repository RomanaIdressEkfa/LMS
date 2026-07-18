<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_login_page_renders(): void
    {
        $this->get('/login')->assertOk()->assertSee('login', false);
    }

    public function test_user_can_log_in_with_valid_credentials(): void
    {
        $user = $this->userWithRole('student', ['password' => Hash::make('secret123')]);

        $response = $this->post('/login', [
            'login' => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_by_phone_works(): void
    {
        $user = $this->userWithRole('student', [
            'phone' => '01700000000',
            'password' => Hash::make('secret123'),
        ]);

        $this->post('/login', ['login' => '01700000000', 'password' => 'secret123'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = $this->userWithRole('student', ['password' => Hash::make('secret123')]);

        $this->from('/login')
            ->post('/login', ['login' => $user->email, 'password' => 'wrong'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_suspended_user_cannot_log_in(): void
    {
        $user = $this->userWithRole('student', [
            'status' => 'suspended',
            'password' => Hash::make('secret123'),
        ]);

        $this->from('/login')
            ->post('/login', ['login' => $user->email, 'password' => 'secret123'])
            ->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_registration_creates_user_with_role_and_logs_in(): void
    {
        $response = $this->post('/register', [
            'name' => 'New Teacher',
            'email' => 'new@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'teacher',
        ]);

        $response->assertRedirect('/dashboard');
        $user = User::where('email', 'new@example.test')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('teacher'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_is_blocked_when_disabled(): void
    {
        Setting::set('allow_registration', false, 'auth', 'bool');

        $this->from('/register')->post('/register', [
            'name' => 'Nope',
            'email' => 'nope@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
        ])->assertSessionHasErrors();

        $this->assertNull(User::where('email', 'nope@example.test')->first());
        $this->assertGuest();
    }

    public function test_registration_rejects_a_privileged_role(): void
    {
        $this->post('/register', [
            'name' => 'Sneaky',
            'email' => 'sneaky@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'super-admin',
        ])->assertSessionHasErrors('role');

        $this->assertNull(User::where('email', 'sneaky@example.test')->first());
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_user_can_log_out(): void
    {
        $user = $this->userWithRole('student');

        $this->actingAs($user)->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }
}
