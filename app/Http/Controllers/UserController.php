<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
        ]);
        $validated['password'] = Hash::make('password');

        $user = User::create($validated);

        if ($request->expectsJson()) {
            return response()->json($user->only(['id', 'name', 'email']));
        }

        return redirect()->route('enrollments')->with('success', 'User created successfully');
    }
}
