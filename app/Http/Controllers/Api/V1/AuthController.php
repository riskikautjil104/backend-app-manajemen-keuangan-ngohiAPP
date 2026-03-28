<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'level' => 'kawasa',
            'is_admin' => false,
        ]);

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $this->userPayload($user),
                'token' => $token,
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        /** @var User $user */
        $user = User::query()->where('email', $credentials['email'])->firstOrFail();
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $this->userPayload($user),
                'token' => $token,
            ],
        ]);
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'level' => $user->level,
        ];
    }
}
