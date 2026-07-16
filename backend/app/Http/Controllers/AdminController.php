<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\PaymentGateway;
use App\Models\User;
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
}
