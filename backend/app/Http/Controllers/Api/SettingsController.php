<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /** The editable site settings, with their current values + types. */
    public const SCHEMA = [
        ['key' => 'site_name', 'label' => 'Site name', 'group' => 'general', 'type' => 'string'],
        ['key' => 'site_tagline', 'label' => 'Tagline', 'group' => 'general', 'type' => 'string'],
        ['key' => 'default_currency', 'label' => 'Default currency', 'group' => 'general', 'type' => 'string'],
        ['key' => 'support_email', 'label' => 'Support email', 'group' => 'general', 'type' => 'string'],
        ['key' => 'allow_registration', 'label' => 'Allow public registration', 'group' => 'auth', 'type' => 'bool'],
        ['key' => 'require_course_approval', 'label' => 'Require admin approval to publish courses', 'group' => 'courses', 'type' => 'bool'],
    ];

    public function index()
    {
        $settings = collect(self::SCHEMA)->map(fn ($s) => array_merge($s, [
            'value' => Setting::get($s['key'], $s['type'] === 'bool' ? false : ''),
        ]));

        return response()->json(['settings' => $settings->groupBy('group')]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'settings' => ['required', 'array'],
        ]);

        $schema = collect(self::SCHEMA)->keyBy('key');

        foreach ($data['settings'] as $key => $value) {
            if (! $schema->has($key)) {
                continue; // ignore unknown keys
            }
            $def = $schema[$key];
            Setting::set($key, $value, $def['group'], $def['type']);
        }

        return $this->index();
    }
}
