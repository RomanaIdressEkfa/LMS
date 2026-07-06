<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    public function index()
    {
        return response()->json([
            'plans' => Plan::withCount('tenants')->orderBy('sort_order')->orderBy('price')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePlan($request);
        $data['slug'] = $this->uniqueSlug($data['name']);

        return response()->json(['plan' => Plan::create($data)], 201);
    }

    public function update(Request $request, Plan $plan)
    {
        $plan->update($this->validatePlan($request));

        return response()->json(['plan' => $plan->fresh()]);
    }

    public function destroy(Plan $plan)
    {
        if ($plan->tenants()->exists()) {
            return response()->json(['message' => 'Move tenants off this plan first.'], 422);
        }
        $plan->delete();

        return response()->json(['message' => 'Plan deleted.']);
    }

    private function validatePlan(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'interval' => ['required', Rule::in(['monthly', 'yearly', 'one_time'])],
            'module_keys' => ['array'],
            'module_keys.*' => ['string'],
            'is_active' => ['boolean'],
        ]);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;
        while (Plan::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }
        return $slug;
    }
}
