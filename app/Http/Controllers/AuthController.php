<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\EventOrganizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return $this->redirectByRole();
        }

        return back()->withErrors([
            'email' => 'Kredensial tidak valid.'
        ])->withInput();
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:user,eo,vendor'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role']
        ]);

        // If EO, create EO profile
        if ($validated['role'] === 'eo') {
            $eo = EventOrganizer::create([
                'user_id' => $user->id,
                'name' => $validated['name'],
                'description' => '',
                'is_active' => false,
                'price_min' => 0,
                'price_max' => 0
            ]);
            
            $user->update(['eo_id' => $eo->id]);
        }

        Auth::login($user);
        return $this->redirectByRole();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    private function redirectByRole()
    {
        $user = Auth::user();
        
        if ($user->role === 'eo') {
            // Check if profile is complete
            if (!$user->eventOrganizer || !$user->eventOrganizer->isProfileComplete()) {
                return redirect()->route('eo.profile')->with('info', 'Silakan lengkapi profile EO Anda terlebih dahulu.');
            }
            return redirect()->route('eo.orders');
        }
        
        if ($user->role === 'vendor') {
            return redirect()->route('vendor.orders');
        }
        
        return redirect()->route('home');
    }
}