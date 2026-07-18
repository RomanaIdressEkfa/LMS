<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Every test gets a fresh in-memory database seeded with the RBAC catalog
     * (all permissions, the default roles, and one demo user per role). Data
     * specific to a test is created within that test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RbacSeeder::class);
    }

    /** Create an active user and give them a role, for acting-as in tests. */
    protected function userWithRole(string $role, array $attrs = []): User
    {
        $user = User::factory()->create($attrs + ['status' => 'active']);
        $user->assignRole($role);

        return $user;
    }
}
