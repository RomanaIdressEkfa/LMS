<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\SiteContentController;
use App\Models\Module;
use App\Models\PaymentGateway;
use App\Models\Plan;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Translations;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

/**
 * Admin panel (Blade): modules, payment gateways and user management. Each
 * action reuses the same model logic as the JSON API; toggles/updates POST to
 * CSRF-protected web routes and redirect back.
 */
class AdminController extends Controller
{
    /* ---------------- Modules ---------------- */
    public function modules()
    {
        return view('dashboard.admin.modules', ['modules' => Module::orderBy('sort_order')->get()]);
    }

    public function toggleModule(Module $module)
    {
        if ($module->is_core) {
            return back()->with('err', 'Core modules cannot be disabled.');
        }
        $module->update(['enabled' => ! $module->enabled]);

        return back()->with('ok', "{$module->name} " . ($module->enabled ? 'enabled' : 'disabled') . '.');
    }

    /* ---------------- Payment gateways ---------------- */
    public function gateways()
    {
        return view('dashboard.admin.gateways', ['gateways' => PaymentGateway::orderBy('sort_order')->get()]);
    }

    public function toggleGateway(PaymentGateway $gateway)
    {
        $gateway->update(['enabled' => ! $gateway->enabled]);

        return back()->with('ok', "{$gateway->name} " . ($gateway->enabled ? 'enabled' : 'disabled') . '.');
    }

    /* ---------------- Users ---------------- */
    public function users(Request $request)
    {
        $users = User::query()
            ->with('roles:id,name')
            ->when($request->search, fn ($q, $s) => $q->where(fn ($w) => $w->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")))
            ->when($request->role, fn ($q, $role) => $q->whereHas('roles', fn ($r) => $r->where('name', $role)))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('dashboard.admin.users', [
            'users' => $users,
            'roles' => Role::orderBy('name')->pluck('name'),
            'search' => $request->search,
            'roleFilter' => $request->role,
        ]);
    }

    public function toggleUserStatus(Request $request, User $user)
    {
        abort_if($user->id === $request->user()->id, 422, 'You cannot change your own status.');
        $user->update(['status' => $user->status === 'active' ? 'suspended' : 'active']);

        return back()->with('ok', "{$user->name} is now {$user->status}.");
    }

    public function setUserRole(Request $request, User $user)
    {
        $data = $request->validate(['role' => ['required', 'exists:roles,name']]);
        $user->syncRoles([$data['role']]);

        return back()->with('ok', "{$user->name}'s role updated to {$data['role']}.");
    }

    /* ---------------- Site content editor ---------------- */

    /** Curated i18n keys exposed for bilingual editing, grouped for the UI. */
    public const TEXT_GROUPS = [
        'Hero' => [
            'ph.badge' => 'Badge', 'ph.h1a' => 'Headline start', 'ph.h1b' => 'Headline highlight', 'ph.h1c' => 'Headline end',
            'ph.heroSub' => 'Subtitle', 'ph.cta1' => 'Primary button', 'ph.cta2' => 'Secondary button',
            'ph.badge1' => 'Pill 1', 'ph.badge2' => 'Pill 2', 'ph.badge3' => 'Pill 3', 'ph.techLabel' => 'Tech strip label',
        ],
        'Section titles' => [
            'ph.whyTitle' => 'Why — title', 'ph.whySub' => 'Why — subtitle', 'ph.statsTitle' => 'Stats — title',
            'ph.techTitle' => 'Tech — title', 'ph.techSub' => 'Tech — subtitle', 'ph.storiesTitle' => 'Stories — title',
            'ph.supportTitle' => 'Support — title', 'ph.faqTitle' => 'FAQ — title',
            'ph.finalTitle' => 'Final CTA — title', 'ph.finalSub' => 'Final CTA — subtitle',
            'home.featured' => 'Featured — title', 'home.seeAll' => 'Featured — see-all',
        ],
    ];

    public function content()
    {
        $textValues = [];
        foreach (self::TEXT_GROUPS as $keys) {
            foreach (array_keys($keys) as $key) {
                $textValues[$key] = Translations::get($key);
            }
        }

        return view('dashboard.admin.content', [
            'content' => SiteContentController::effectiveContent(),
            'textValues' => $textValues,
            'groups' => self::TEXT_GROUPS,
            'canManage' => auth()->user()->can('settings.manage'),
        ]);
    }

    /* ---------------- Settings ---------------- */
    public function settings()
    {
        $settings = collect(SettingsController::SCHEMA)
            ->map(fn ($s) => array_merge($s, [
                'value' => Setting::get($s['key'], SettingsController::DEFAULTS[$s['key']] ?? ($s['type'] === 'bool' ? false : '')),
            ]))
            ->groupBy('group');

        return view('dashboard.admin.settings', [
            'groups' => $settings,
            'canManage' => auth()->user()->can('settings.manage'),
        ]);
    }

    public function updateSettings(Request $request)
    {
        abort_unless($request->user()->can('settings.manage'), 403);

        foreach (SettingsController::SCHEMA as $s) {
            $key = $s['key'];
            $value = $s['type'] === 'bool'
                ? $request->boolean("settings.$key")
                : (string) $request->input("settings.$key", '');
            Setting::set($key, $value, $s['group'], $s['type']);
        }

        return back()->with('ok', 'Settings saved.');
    }

    /* ---------------- Roles (read-only view) ---------------- */
    public function roles()
    {
        $roles = Role::with('permissions:id,name')->orderBy('id')->get()->map(fn (Role $r) => [
            'name' => $r->name,
            'users_count' => $r->users()->count(),
            'protected' => in_array($r->name, ['super-admin', 'admin', 'teacher', 'student'], true),
            'permissions' => $r->permissions->pluck('name')->sort()->values(),
        ]);

        return view('dashboard.admin.roles', compact('roles'));
    }

    /* ---------------- Plans ---------------- */
    public function plans()
    {
        return view('dashboard.admin.plans', ['plans' => Plan::withCount('tenants')->orderBy('sort_order')->orderBy('price')->get()]);
    }

    public function deletePlan(Plan $plan)
    {
        if ($plan->tenants()->exists()) {
            return back()->with('err', 'Cannot delete a plan that has tenants.');
        }
        $plan->delete();

        return back()->with('ok', 'Plan deleted.');
    }

    /* ---------------- Tenants (read-only view) ---------------- */
    public function tenants()
    {
        return view('dashboard.admin.tenants', ['tenants' => Tenant::with('plan:id,name')->latest()->get()]);
    }
}
