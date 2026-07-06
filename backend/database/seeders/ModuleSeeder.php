<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;

class ModuleSeeder extends Seeder
{
    /**
     * The addon catalog. `is_core` modules cannot be turned off; everything
     * else is a toggle in the admin panel — this is your "enable only what you
     * use" system.
     */
    public const MODULES = [
        ['key' => 'courses',      'name' => 'Courses',        'icon' => 'graduation-cap', 'category' => 'education', 'is_core' => true,  'enabled' => true,  'description' => 'Create, sell and manage courses & lessons.'],
        ['key' => 'roles',        'name' => 'Roles & Permissions', 'icon' => 'shield-check', 'category' => 'system', 'is_core' => true, 'enabled' => true, 'description' => 'Custom roles with granular permissions.'],
        ['key' => 'live_classes', 'name' => 'Live Classes',   'icon' => 'video',          'category' => 'education', 'is_core' => false, 'enabled' => true,  'description' => 'Teachers host live video sessions.'],
        ['key' => 'quizzes',      'name' => 'Quizzes',        'icon' => 'list-checks',    'category' => 'education', 'is_core' => false, 'enabled' => true,  'description' => 'Assessments and auto-grading.'],
        ['key' => 'certificates', 'name' => 'Certificates',   'icon' => 'award',          'category' => 'education', 'is_core' => false, 'enabled' => true,  'description' => 'Issue completion certificates.'],
        ['key' => 'store',        'name' => 'Store',          'icon' => 'shopping-bag',   'category' => 'commerce', 'is_core' => false, 'enabled' => true,  'description' => 'Sell digital products & bundles.'],
        ['key' => 'forums',       'name' => 'Forums',         'icon' => 'messages-square','category' => 'community','is_core' => false, 'enabled' => true,  'description' => 'Community Q&A and discussions.'],
        ['key' => 'events',       'name' => 'Events',         'icon' => 'calendar',       'category' => 'community','is_core' => false, 'enabled' => true,  'description' => 'Schedule and sell tickets to events.'],
        ['key' => 'jobs',         'name' => 'Jobs Board',     'icon' => 'briefcase',      'category' => 'community','is_core' => false, 'enabled' => false, 'description' => 'Post and browse job listings.'],
        ['key' => 'wallet',       'name' => 'Wallet',         'icon' => 'wallet',         'category' => 'commerce', 'is_core' => false, 'enabled' => true,  'description' => 'Student & instructor balances and payouts.'],
        ['key' => 'blog',         'name' => 'Blog',           'icon' => 'newspaper',      'category' => 'content',  'is_core' => false, 'enabled' => false, 'description' => 'Content marketing articles.'],
        ['key' => 'affiliates',   'name' => 'Affiliates',     'icon' => 'share-2',        'category' => 'commerce', 'is_core' => false, 'enabled' => false, 'description' => 'Referral commissions.'],
    ];

    public function run(): void
    {
        foreach (self::MODULES as $i => $m) {
            Module::updateOrCreate(
                ['key' => $m['key']],
                array_merge($m, ['sort_order' => $i]),
            );
        }
    }
}
