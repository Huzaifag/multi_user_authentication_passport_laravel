<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;


class AuthController extends Controller
{
    public function register(Request $request)
{
    $request->validate([
        'name' => 'required|string',
        'email' => 'required|string|email|unique:users',
        'password' => 'required|string|min:8',
        'role' => 'required|in:admin,manager,employee',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'role' => $request->role,
    ]);

    return response()->json(['message' => 'User created successfully']);
}
public function login(Request $request)
{
    // Validate request
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    // Check if user exists
    $user = User::where('email', $request->email)->first();

    // Verify password
    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'message' => 'Invalid email or password'
        ], 401);
    }

    // Revoke previous tokens (optional)
    $user->tokens()->delete();

    // Generate access token
    $token = $user->createToken('auth_token')->accessToken;

    // Determine the message based on the user's role
    $roleMessage = '';
    switch ($user->role) {
        case 'admin':
            $roleMessage = 'Admin login successful';
            break;
        case 'manager':
            $roleMessage = 'Manager login successful';
            break;
        case 'user':
            $roleMessage = 'User login successful';
            break;
        default:
            $roleMessage = 'Login successful';
    }

    // Return success response with role message
    return response()->json([
        'message' => $roleMessage,
        'token' => $token,
        'user' => $user
    ]);
}

}
