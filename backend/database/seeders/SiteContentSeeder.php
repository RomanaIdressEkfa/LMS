<?php

namespace Database\Seeders;

use App\Http\Controllers\Api\SiteContentController;
use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Imports the editable marketing content into the database as the
 * `site_content` JSON setting, so the public pages are DB-backed from the
 * first run (not just falling back to code defaults until an admin saves).
 *
 * Idempotent and non-destructive: if a `site_content` row already exists
 * (i.e. an admin has customised it) it is left untouched.
 */
class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        if (Setting::get('site_content') === null) {
            Setting::set('site_content', SiteContentController::DEFAULTS, 'content', 'json');
            $this->command?->info('Seeded site_content from defaults.');
        } else {
            $this->command?->info('site_content already present — left as-is.');
        }
    }
}
