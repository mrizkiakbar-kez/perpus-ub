<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Member;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        } elseif (session()->has('member_id')) {
            return redirect()->route('member.dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required',
            'password' => 'required',
        ]);

        $login = $credentials['login'];
        $password = $credentials['password'];

        // 1. Try to authenticate as Admin user (email, name, or email prefix)
        $user = User::where('email', $login)
            ->orWhere('name', $login)
            ->orWhere('email', 'like', $login . '@%')
            ->first();

        if ($user) {
            $adminAuthenticated = false;
            try {
                if (Hash::check($password, $user->password)) {
                    $adminAuthenticated = true;
                }
            } catch (\RuntimeException $e) {
                // Runtime fallback for plain text password
                if ($password === $user->password) {
                    $adminAuthenticated = true;
                    // Auto-hash it to bcrypt so it is secure for future logins
                    $user->password = Hash::make($password);
                    $user->save();
                }
            }

            if ($adminAuthenticated) {
                Auth::login($user);
                $request->session()->regenerate();
                return redirect()->route('admin.dashboard')->with('success', 'Berhasil login sebagai admin!');
            }
        }

        // 2. Try to authenticate as Member (email, kode_anggota, nama, or email prefix)
        $member = Member::where('email', $login)
            ->orWhere('kode_anggota', $login)
            ->orWhere('nama', $login)
            ->orWhere('email', 'like', $login . '@%')
            ->first();
        
        if ($member) {
            $memberAuthenticated = false;
            try {
                if (Hash::check($password, $member->password)) {
                    $memberAuthenticated = true;
                }
            } catch (\RuntimeException $e) {
                // Runtime fallback for plain text password
                if ($password === $member->password) {
                    $memberAuthenticated = true;
                    // Auto-hash it to bcrypt using mutator
                    $member->password = $password;
                    $member->save();
                }
            }

            if ($memberAuthenticated) {
                session(['member_id' => $member->id, 'member' => $member]);
                $request->session()->regenerate();
                return redirect()->route('member.dashboard')->with('success', 'Berhasil login sebagai anggota!');
            }
        }

        return back()->withErrors([
            'login' => 'Email/Username/Kode Anggota atau password salah.',
        ])->onlyInput('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        session()->forget(['member_id', 'member']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Berhasil logout!');
    }
}

