<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RbacSeeder::class,
            ModuleSeeder::class,
            PaymentGatewaySeeder::class,
            CourseSeeder::class,
            LiveQuizSeeder::class,
            PlanTenantSeeder::class,
        ]);

        // Default site settings.
        Setting::set('site_name', 'Nova LMS', 'general');
        Setting::set('site_tagline', 'Learn Without Limits', 'general');
        Setting::set('default_currency', 'USD', 'general');
        Setting::set('allow_registration', true, 'auth', 'bool');
    }
}
