<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Show the registration form
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }
    
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|min:3|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'sometimes|string|in:admin,cashier',
        ]);

        if ($validator->fails()) {
            return $request->wantsJson()
                ? response()->json(['errors' => $validator->errors()], 422)
                : back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'cashier',
        ]);

        Auth::login($user); // Auto login after registration

        if ($request->wantsJson()) {
            return response()->json([
                'user' => $user,
                'token' => $user->createToken('auth_token')->plainTextToken
            ], 201);
        }

        return redirect()->route('dashboard');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $request->wantsJson()
                ? response()->json(['errors' => $validator->errors()], 422)
                : back()->withErrors($validator)->withInput();
        }

        // Check if the input is email or username
        $field = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [$field => $request->username, 'password' => $request->password];

        if (!Auth::attempt($credentials, $request->filled('remember'))) {
            $message = 'Invalid login credentials';
            return $request->wantsJson()
                ? response()->json(['message' => $message], 401)
                : back()->withErrors(['username' => $message])->withInput();
        }

        $user = User::where($field, $request->username)->firstOrFail();

        if ($request->wantsJson()) {
            return response()->json([
                'user' => $user,
                'token' => $user->createToken('auth_token')->plainTextToken
            ]);
        }

        return redirect()->intended('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Logged out successfully']);
        }

        return redirect('/');
    }

    /**
     * Display user profile
     */
    public function profile()
    {
        $user = Auth::user();
        return view('profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'username' => 'sometimes|string|min:3|unique:users,username,' . $user->id,
        ]);

        if ($validator->fails()) {
            return $request->wantsJson()
                ? response()->json(['errors' => $validator->errors()], 422)
                : back()->withErrors($validator)->withInput();
        }

        $user->update($request->only(['name', 'email', 'username']));

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => $user
            ]);
        }

        return back()->with('success', 'Profile updated successfully');
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $request->wantsJson()
                ? response()->json(['errors' => $validator->errors()], 422)
                : back()->withErrors($validator)->withInput();
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            $message = 'Current password is incorrect';
            return $request->wantsJson()
                ? response()->json(['message' => $message], 400)
                : back()->withErrors(['current_password' => $message]);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Password updated successfully']);
        }

        return back()->with('success', 'Password updated successfully');
    }
}