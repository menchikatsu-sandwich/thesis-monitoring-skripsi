<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthApiController extends Controller
{
    // =========================
    // VIEW
    // =========================
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    // =========================
    // REGISTER
    // =========================
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|in:student,lecturer',
            'nip_nim' => 'required|string|unique:users,nip_nim'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'nip_nim' => $request->nip_nim,
        ]);

        // API mode
        if ($request->wantsJson()) {
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Registrasi berhasil',
                'user' => $user,
                'token' => $token
            ], 201);
        }

        // WEB mode
        return redirect()->route('login')->with('success', 'Registrasi berhasil! Silakan login.');
    }

    // =========================
    // LOGIN
    // =========================
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Email atau password salah'
                ], 401);
            }

            return back()->withErrors(['email' => 'Email atau password salah']);
        }

        // API mode
        if ($request->wantsJson()) {
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login berhasil',
                'user' => $user,
                'token' => $token
            ]);
        }

        // WEB mode
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    // =========================
    // LOGOUT
    // =========================
    public function logout(Request $request)
    {
        if ($request->wantsJson()) {
            $request->user()->tokens()->delete();

            return response()->json([
                'message' => 'Logout berhasil'
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    // =========================
    // ME
    // =========================
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()
        ]);
    }
}