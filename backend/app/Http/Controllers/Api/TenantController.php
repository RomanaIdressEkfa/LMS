<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::with('plan:id,name,price')->latest()->get()->map(fn (Tenant $t) => [
            'id' => $t->id,
            'name' => $t->name,
            'slug' => $t->slug,
            'owner_name' => $t->owner_name,
            'owner_email' => $t->owner_email,
            'plan' => $t->plan,
            'status' => $t->status,
            'primary_color' => $t->primary_color,
            'effective_price' => $t->effectivePrice(),
            'enabled_modules' => $t->enabledModuleKeys(),
        ]);

        return response()->json(['tenants' => $tenants]);
    }

    public function show(Tenant $tenant)
    {
        return response()->json(['tenant' => array_merge($tenant->load('plan')->toArray(), [
            'enabled_modules' => $tenant->enabledModuleKeys(),
            'effective_price' => $tenant->effectivePrice(),
        ])]);
    }

    public function store(Request $request)
    {
        $data = $this->validateTenant($request, true);
        $data['slug'] = $this->uniqueSlug($data['name']);
        // Start from the plan's modules as the tenant's overrides baseline.
        if (! empty($data['plan_id'])) {
            $data['module_overrides'] = Plan::find($data['plan_id'])?->module_keys ?? [];
        }
        $data['trial_ends_at'] = now()->addDays(14);

        return response()->json(['tenant' => Tenant::create($data)], 201);
    }

    public function update(Request $request, Tenant $tenant)
    {
        $data = $this->validateTenant($request, false);
        $tenant->update($data);

        return response()->json(['tenant' => $tenant->fresh()->load('plan')]);
    }

    /** Turn a single module on/off for this tenant (per-customer control). */
    public function toggleModule(Request $request, Tenant $tenant)
    {
        $key = $request->validate(['key' => ['required', 'string']])['key'];

        $modules = $tenant->enabledModuleKeys();
        $modules = in_array($key, $modules, true)
            ? array_values(array_diff($modules, [$key]))
            : [...$modules, $key];

        $tenant->update(['module_overrides' => $modules]);

        return response()->json(['enabled_modules' => $modules]);
    }

    public function setStatus(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['trial', 'active', 'suspended'])],
        ]);
        $tenant->update($data);

        return response()->json(['tenant' => $tenant]);
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete();

        return response()->json(['message' => 'Tenant deleted.']);
    }

    private function validateTenant(Request $request, bool $creating): array
    {
        return $request->validate([
            'name' => [$creating ? 'required' : 'sometimes', 'string', 'max:150'],
            'owner_name' => ['nullable', 'string', 'max:150'],
            'owner_email' => ['nullable', 'email'],
            'plan_id' => ['nullable', Rule::exists('plans', 'id')],
            'price_override' => ['nullable', 'numeric', 'min:0'],
            'primary_color' => ['nullable', 'string', 'max:9'],
        ]);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;
        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }
        return $slug;
    }
}
