<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    public function createUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'phone_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9|unique:users',
        ]);

        $user = User::create([
            'custom_id' => $this->generateCustomId(),
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'points' => 100,
        ]);

        $user->save();

        return response()->json(['message' => 'User created successfully']);
    }

    public function login()
    {
        $credentials = request(['name', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
        ]);
    }

    private function generateCustomId()
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $customId = '';

        for ($i = 0; $i < 5; $i++) {
            $index = random_int(0, strlen($characters) - 1);
            $customId .= $characters[$index];
        }

        return $customId;
    }
}
