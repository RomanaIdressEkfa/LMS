<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    /**
     * Register a new account. Only self-registerable roles are allowed here
     * (a visitor cannot make themselves an admin).
     */
    public function register(Request $request)
    {
        if (! Setting::get('allow_registration', true)) {
            return response()->json(['message' => 'Registration is currently disabled.'], 403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['student', 'teacher', 'organization'])],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'status' => 'active',
        ]);

        $user->assignRole($data['role']);

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'user' => $user->profile(),
            'token' => $token,
        ], 201);
    }

    /**
     * Log in with email OR phone + password.
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => ['required', 'string'], // email or phone
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['login'])
            ->orWhere('phone', $data['login'])
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['These credentials do not match our records.'],
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'login' => ['Your account is not active. Please contact support.'],
            ]);
        }

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'user' => $user->profile(),
            'token' => $token,
        ]);
    }

    /**
     * Current authenticated user with roles + permissions.
     */
    public function me(Request $request)
    {
        return response()->json(['user' => $request->user()->profile()]);
    }

    /**
     * Revoke the token used for the current request.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }
}
