<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RbacSeeder extends Seeder
{
    /**
     * Permission catalog, grouped by area. The frontend renders these groups
     * as collapsible sections in the role builder so an admin can tick exactly
     * what each custom role may do.
     *
     * @var array<string, string[]>
     */
    public const PERMISSIONS = [
        'Dashboard'   => ['dashboard.view'],
        'Users'       => ['users.view', 'users.create', 'users.update', 'users.delete', 'users.impersonate'],
        'Roles'       => ['roles.view', 'roles.create', 'roles.update', 'roles.delete'],
        'Courses'     => ['courses.view', 'courses.create', 'courses.update', 'courses.delete', 'courses.publish', 'courses.approve'],
        'Lessons'     => ['lessons.view', 'lessons.create', 'lessons.update', 'lessons.delete'],
        'Enrollments' => ['enrollments.view', 'enrollments.manage'],
        'Live Classes'=> ['live.view', 'live.host', 'live.manage'],
        'Quizzes'     => ['quizzes.view', 'quizzes.create', 'quizzes.grade'],
        'Payments'    => ['payments.view', 'payments.refund', 'payouts.manage'],
        'Gateways'    => ['gateways.view', 'gateways.manage'],
        'Modules'     => ['modules.view', 'modules.manage'],
        'Settings'    => ['settings.view', 'settings.manage'],
        'Reports'     => ['reports.view'],
        'Platform'    => ['tenants.view', 'tenants.manage', 'plans.manage'],
    ];

    /**
     * Default roles → the permissions they receive out of the box.
     * '*' means "every permission" (super admin).
     *
     * @var array<string, array{label:string, permissions: string[]|string}>
     */
    public const ROLES = [
        'super-admin'  => ['label' => 'Super Admin',   'permissions' => '*'],
        'admin'        => ['label' => 'Admin',         'permissions' => [
            'dashboard.view', 'users.view', 'users.create', 'users.update',
            'roles.view', 'courses.view', 'courses.approve', 'courses.publish',
            'enrollments.view', 'payments.view', 'gateways.view', 'modules.view',
            'settings.view', 'settings.manage', 'reports.view',
        ]],
        'teacher'      => ['label' => 'Teacher',       'permissions' => [
            'dashboard.view', 'courses.view', 'courses.create', 'courses.update',
            'lessons.view', 'lessons.create', 'lessons.update', 'lessons.delete',
            'live.view', 'live.host', 'quizzes.view', 'quizzes.create', 'quizzes.grade',
            'enrollments.view',
        ]],
        'student'      => ['label' => 'Student',       'permissions' => [
            'dashboard.view', 'courses.view', 'lessons.view', 'live.view', 'quizzes.view',
        ]],
        'organization' => ['label' => 'Organization',  'permissions' => [
            'dashboard.view', 'users.view', 'users.create', 'courses.view',
            'courses.create', 'reports.view',
        ]],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 1) Create every permission.
        $all = [];
        foreach (self::PERMISSIONS as $group) {
            foreach ($group as $name) {
                Permission::findOrCreate($name, 'web');
                $all[] = $name;
            }
        }

        // 2) Create roles and assign their permission sets.
        foreach (self::ROLES as $name => $def) {
            $role = Role::findOrCreate($name, 'web');
            $role->syncPermissions($def['permissions'] === '*' ? $all : $def['permissions']);
        }

        // 3) Seed one demo account per role so you can log in immediately.
        $demoUsers = [
            ['name' => 'Super Admin', 'email' => 'super@novalms.test',   'role' => 'super-admin'],
            ['name' => 'Site Admin',  'email' => 'admin@novalms.test',   'role' => 'admin'],
            ['name' => 'Tara Teacher','email' => 'teacher@novalms.test', 'role' => 'teacher'],
            ['name' => 'Sam Student', 'email' => 'student@novalms.test', 'role' => 'student'],
        ];

        foreach ($demoUsers as $u) {
            $user = User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'status' => 'active',
                ]
            );
            $user->syncRoles([$u['role']]);
        }
    }
}
