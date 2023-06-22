<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }
    public function register(Request $request)
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



        return response()->json(['message' => 'User registered successfully']);
    }
    public function login(Request $request)
    {
        $user = User::where('name', $request->input('name'))
            ->where('phone_number', $request->input('phone_number'))
            ->first();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $token = auth()->login($user);

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
