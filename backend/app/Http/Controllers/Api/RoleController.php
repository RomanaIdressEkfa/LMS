<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Database\Seeders\RbacSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * The Roles & Permissions builder API.
 * Lets an authorized admin create unlimited custom roles and tick exactly
 * which permissions each one holds.
 */
class RoleController extends Controller
{
    /** List roles with their permission names + user counts. */
    public function index()
    {
        // Note: Role::withCount('users') breaks because the relation is built on
        // an instance without guard_name, so we count per loaded role instead
        // (each role here already has guard_name set, resolving the user model).
        $roles = Role::with('permissions:id,name')->orderBy('id')->get()
            ->map(fn (Role $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'users_count' => $r->users()->count(),
                'is_protected' => in_array($r->name, ['super-admin', 'admin', 'teacher', 'student']),
                'permissions' => $r->permissions->pluck('name'),
            ]);

        return response()->json(['roles' => $roles]);
    }

    /** The full permission catalog, grouped, for rendering the builder UI. */
    public function catalog()
    {
        return response()->json(['groups' => RbacSeeder::PERMISSIONS]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ]);

        $role = Role::create(['name' => Str::slug($data['name']), 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        return response()->json(['role' => $this->show($role->id)->getData()->role], 201);
    }

    public function show(int $id)
    {
        $role = Role::with('permissions:id,name')->findOrFail($id);

        return response()->json(['role' => [
            'id' => $role->id,
            'name' => $role->name,
            'permissions' => $role->permissions->pluck('name'),
        ]]);
    }

    public function update(Request $request, int $id)
    {
        $role = Role::findOrFail($id);

        $data = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ]);

        // super-admin always keeps every permission.
        if ($role->name === 'super-admin') {
            $role->syncPermissions(Permission::pluck('name'));
        } else {
            $role->syncPermissions($data['permissions'] ?? []);
        }

        return $this->show($id);
    }

    public function destroy(int $id)
    {
        $role = Role::findOrFail($id);

        if (in_array($role->name, ['super-admin', 'admin', 'teacher', 'student'])) {
            return response()->json(['message' => 'Built-in roles cannot be deleted.'], 422);
        }

        $role->delete();

        return response()->json(['message' => 'Role deleted.']);
    }
}
