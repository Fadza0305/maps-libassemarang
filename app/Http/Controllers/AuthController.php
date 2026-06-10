<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // ==========================================
    // API AUTH (Sanctum token — used by mobile/SPA)
    // ==========================================

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'owner'
        ]);

        return response()->json([
            'message' => 'Register success',
            'user'    => $user
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Login failed'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login success',
            'token'   => $token,
            'user'    => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout success']);
    }

    // ==========================================
    // WEB AUTH (Session — used by Blade views)
    // ==========================================

    /**
     * Handle web login form submission, start a session.
     * This makes auth()->check() and auth()->user() available in Blade.
     */
    public function webLogin(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $role = Auth::user()->role;
        $redirectUrl = '/map';
        
        if ($role === 'admin') {
            $redirectUrl = '/admin/dashboard';
        } elseif ($role === 'owner') {
            $redirectUrl = '/owner/dashboard';
        }

        if ($request->wantsJson()) {
            $token = Auth::user()->createToken('auth_token')->plainTextToken;
            return response()->json([
                'message' => 'Login success',
                'token'   => $token,
                'user'    => Auth::user(),
                'redirect'=> $redirectUrl
            ]);
        }

        return redirect()->intended($redirectUrl);
    }

    /**
     * Handle web logout — destroy the session.
     */
    public function webLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    // ==========================================
    // ADMIN USER MANAGEMENT
    // ==========================================

    public function indexUsers()
    {
        $users = User::all();
        return response()->json($users);
    }

    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent admin from deleting themselves
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Cannot delete your own account'], 400);
        }

        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
}
