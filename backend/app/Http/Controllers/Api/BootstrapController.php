<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\PaymentGateway;
use App\Models\Setting;
use App\Models\Tenant;
use Illuminate\Http\Request;

/**
 * Public app config consumed by the frontend on load: brand settings, which
 * modules are enabled, and which payment gateways are available at checkout.
 *
 * Tenant-aware: when an `X-Tenant` header (a tenant slug) is present, the
 * response is scoped to that tenant — only the modules their plan enables are
 * returned, and branding reflects the tenant. This is what lets one install
 * serve many resold academies, each seeing only what they pay for.
 */
class BootstrapController extends Controller
{
    public function __invoke(Request $request)
    {
        $tenant = $this->resolveTenant($request);

        $modules = Module::where('enabled', true)->orderBy('sort_order')
            ->get(['key', 'name', 'icon', 'category']);

        if ($tenant) {
            $allowed = $tenant->enabledModuleKeys();
            $modules = $modules->whereIn('key', $allowed)->values();
        }

        return response()->json([
            'tenant' => $tenant ? [
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'primary_color' => $tenant->primary_color,
                'status' => $tenant->status,
            ] : null,
            'settings' => [
                'site_name' => $tenant?->name ?? Setting::get('site_name', 'LMS'),
                'site_logo' => Setting::get('site_logo') ? \Storage::disk('public')->url(Setting::get('site_logo')) : null,
                'site_tagline' => Setting::get('site_tagline', 'Learn Without Limits'),
                'default_language' => Setting::get('default_language', 'en'),
                'default_currency' => Setting::get('default_currency', 'USD'),
                'allow_registration' => Setting::get('allow_registration', true),
                // Appearance (tenant colour wins if present, else the global setting)
                'primary_color' => $tenant?->primary_color ?? Setting::get('primary_color', '#2563ff'),
                'home_show_stats' => Setting::get('home_show_stats', true),
                'home_show_tech' => Setting::get('home_show_tech', true),
                'home_show_stories' => Setting::get('home_show_stories', true),
                'home_show_support' => Setting::get('home_show_support', true),
                'home_show_faq' => Setting::get('home_show_faq', true),
            ],
            // Admin bilingual overrides for i18n keys ({ key: { en, bn } }).
            'text_overrides' => Setting::get('text_overrides', (object) []),
            'modules' => $modules,
            'gateways' => PaymentGateway::where('enabled', true)->orderBy('sort_order')
                ->get(['key', 'name', 'logo', 'currency']),
        ]);
    }

    private function resolveTenant(Request $request): ?Tenant
    {
        $slug = $request->header('X-Tenant');
        if (! $slug) {
            return null;
        }
        return Tenant::with('plan')->where('slug', $slug)->where('status', '!=', 'suspended')->first();
    }
}
