<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /** Paginated user list with search + role filter. */
    public function index(Request $request)
    {
        $users = User::query()
            ->with('roles:id,name')
            ->when($request->search, fn ($q, $s) =>
                $q->where(fn ($w) => $w->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")))
            ->when($request->role, fn ($q, $role) =>
                $q->whereHas('roles', fn ($r) => $r->where('name', $role)))
            ->when($request->status, fn ($q, $st) => $q->where('status', $st))
            ->latest()
            ->paginate(15)
            ->through(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => $u->phone,
                'status' => $u->status,
                'roles' => $u->roles->pluck('name'),
                'created_at' => $u->created_at,
            ]);

        return response()->json($users);
    }

    /** Admin creates a user and assigns roles. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8'],
            'roles' => ['array'],
            'roles.*' => [Rule::exists('roles', 'name')],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'status' => 'active',
        ]);
        $user->syncRoles($data['roles'] ?? ['student']);

        return response()->json(['user' => $user->profile()], 201);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('users')->ignore($user->id)],
            'status' => ['sometimes', Rule::in(['active', 'suspended', 'pending'])],
        ]);

        $user->update($data);

        return response()->json(['user' => $user->profile()]);
    }

    /** Replace a user's roles. */
    public function setRoles(Request $request, User $user)
    {
        $data = $request->validate([
            'roles' => ['required', 'array'],
            'roles.*' => [Rule::exists('roles', 'name')],
        ]);

        // Guard: don't let an admin strip the last super-admin.
        if ($user->hasRole('super-admin') && ! in_array('super-admin', $data['roles'])
            && Role::findByName('super-admin')->users()->count() <= 1) {
            return response()->json(['message' => 'Cannot remove the last super admin.'], 422);
        }

        $user->syncRoles($data['roles']);

        return response()->json(['user' => $user->profile()]);
    }

    /** Suspend / reactivate. */
    public function toggleStatus(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'You cannot suspend your own account.'], 422);
        }
        $user->update(['status' => $user->status === 'active' ? 'suspended' : 'active']);

        return response()->json(['user' => $user->profile()]);
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }
        if ($user->hasRole('super-admin')) {
            return response()->json(['message' => 'Super admins cannot be deleted here.'], 422);
        }
        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }

    /**
     * Impersonate a user: issue a token acting as them. Restricted to the
     * users.impersonate permission (super admin by default).
     */
    public function impersonate(Request $request, User $user)
    {
        $token = $user->createToken('impersonation')->plainTextToken;

        return response()->json([
            'user' => $user->profile(),
            'token' => $token,
            'message' => "You are now acting as {$user->name}.",
        ]);
    }

    /** Roles available to assign (for the picker). */
    public function assignableRoles()
    {
        return response()->json(['roles' => Role::orderBy('id')->pluck('name')]);
    }
}
