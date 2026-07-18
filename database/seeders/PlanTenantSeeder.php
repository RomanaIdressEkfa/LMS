<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;
use App\Models\Tenant;

class PlanTenantSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter', 'slug' => 'starter', 'price' => 29, 'interval' => 'monthly',
                'description' => 'Everything to launch a small academy.',
                'module_keys' => ['courses', 'roles', 'quizzes', 'wallet'],
                'sort_order' => 0,
            ],
            [
                'name' => 'Growth', 'slug' => 'growth', 'price' => 79, 'interval' => 'monthly',
                'description' => 'Sell courses, run live classes and events.',
                'module_keys' => ['courses', 'roles', 'quizzes', 'certificates', 'live_classes', 'store', 'wallet', 'forums'],
                'sort_order' => 1,
            ],
            [
                'name' => 'Enterprise', 'slug' => 'enterprise', 'price' => 199, 'interval' => 'monthly',
                'description' => 'Every module, unlimited scale.',
                'module_keys' => ['courses', 'roles', 'quizzes', 'certificates', 'live_classes', 'store', 'wallet', 'forums', 'events', 'jobs', 'blog', 'affiliates'],
                'sort_order' => 2,
            ],
        ];

        foreach ($plans as $p) {
            Plan::updateOrCreate(['slug' => $p['slug']], $p);
        }

        $growth = Plan::where('slug', 'growth')->first();

        Tenant::updateOrCreate(
            ['slug' => 'acme-academy'],
            [
                'name' => 'Acme Academy',
                'owner_name' => 'Jordan Lee',
                'owner_email' => 'owner@acme.test',
                'plan_id' => $growth->id,
                'module_overrides' => $growth->module_keys, // starts equal to plan
                'primary_color' => '#7c3aed',
                'status' => 'active',
            ]
        );
    }
}
