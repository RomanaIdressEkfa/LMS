<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /** Upload a site logo image; stores the path in the `site_logo` setting. */
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => ['required', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
        ]);

        $old = Setting::get('site_logo');
        if ($old) {
            Storage::disk('public')->delete($old);
        }

        $path = $request->file('logo')->store('branding', 'public');
        Setting::set('site_logo', $path, 'appearance');

        return response()->json([
            'site_logo' => $path,
            'url' => Storage::disk('public')->url($path),
        ]);
    }

    /** Remove the custom logo (revert to the default mark). */
    public function removeLogo()
    {
        $old = Setting::get('site_logo');
        if ($old) {
            Storage::disk('public')->delete($old);
        }
        Setting::set('site_logo', '', 'appearance');

        return response()->json(['message' => 'Logo removed.']);
    }

    /** The editable site settings, with their current values + types. */
    public const SCHEMA = [
        ['key' => 'site_name', 'label' => 'Site name', 'group' => 'general', 'type' => 'string'],
        ['key' => 'site_tagline', 'label' => 'Tagline', 'group' => 'general', 'type' => 'string'],
        ['key' => 'default_language', 'label' => 'Default language (en or bn)', 'group' => 'general', 'type' => 'string'],
        ['key' => 'default_currency', 'label' => 'Default currency', 'group' => 'general', 'type' => 'string'],
        ['key' => 'support_email', 'label' => 'Support email', 'group' => 'general', 'type' => 'string'],
        ['key' => 'allow_registration', 'label' => 'Allow public registration', 'group' => 'auth', 'type' => 'bool'],
        ['key' => 'require_course_approval', 'label' => 'Require admin approval to publish courses', 'group' => 'courses', 'type' => 'bool'],

        // ---- Appearance (super-admin theming) ----
        ['key' => 'primary_color', 'label' => 'Primary brand color', 'group' => 'appearance', 'type' => 'color'],
        ['key' => 'home_show_stats', 'label' => 'Homepage: show success-stats band', 'group' => 'appearance', 'type' => 'bool'],
        ['key' => 'home_show_tech', 'label' => 'Homepage: show technology grid', 'group' => 'appearance', 'type' => 'bool'],
        ['key' => 'home_show_stories', 'label' => 'Homepage: show success stories', 'group' => 'appearance', 'type' => 'bool'],
        ['key' => 'home_show_support', 'label' => 'Homepage: show support section', 'group' => 'appearance', 'type' => 'bool'],
        ['key' => 'home_show_faq', 'label' => 'Homepage: show FAQ', 'group' => 'appearance', 'type' => 'bool'],
    ];

    /** Sensible defaults per key (used when nothing is saved yet). */
    public const DEFAULTS = [
        'primary_color' => '#2563ff',
        'home_show_stats' => true,
        'home_show_tech' => true,
        'home_show_stories' => true,
        'home_show_support' => true,
        'home_show_faq' => true,
    ];

    public function index()
    {
        $settings = collect(self::SCHEMA)->map(fn ($s) => array_merge($s, [
            'value' => Setting::get($s['key'], self::DEFAULTS[$s['key']] ?? ($s['type'] === 'bool' ? false : '')),
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
