<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function showAllUsers()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(10);

        if ($users->isEmpty()) {
            return response()->json(['users' => []]);
        }

        return response()->json(['users' => $users]);
    }
    public function showOneUser(Request $request)
    {
        $user =  User::findOrFail($request->user_id);

        return response()->json(['user' => $user]);
    }

    public function blockUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'action' => 'required|in:block,unblock',
        ]);

        $user = User::findOrFail($request->user_id);
        $blocked = ($request->action === 'block');

        $user->blocked = $blocked;
        $user->save();

        $message = $blocked ? 'User blocked successfully' : 'User unblocked successfully';

        return response()->json(['message' => $message]);
    }
}
