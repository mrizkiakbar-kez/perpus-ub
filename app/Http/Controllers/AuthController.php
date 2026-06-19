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
            if (Auth::user()->role === 'admin') {
                return redirect()->route('admin.dashboard');
            } else {
                return redirect()->route('member.dashboard');
            }
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

        // 1. Try to authenticate as Admin/User (email, name, or email prefix)
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
                
                if ($user->role === 'admin') {
                    return redirect()->route('admin.dashboard')->with('success', 'Berhasil login sebagai admin!');
                } else {
                    // For members in users table: find or create corresponding member record
                    $member = Member::where('email', $user->email)->first();
                    if (!$member) {
                        $latestMember = Member::latest()->first();
                        $latestId = $latestMember ? $latestMember->id + 1 : 1;
                        $kodeAnggota = 'MBR' . str_pad($latestId, 3, '0', STR_PAD_LEFT);
                        
                        $member = Member::create([
                            'kode_anggota' => $kodeAnggota,
                            'nama' => $user->name,
                            'email' => $user->email,
                            'password' => $password, // Mutator hashes it
                            'telepon' => '-',
                            'alamat' => '-',
                            'role' => 'member',
                        ]);
                    }
                    session(['member_id' => $member->id, 'member' => $member]);
                    return redirect()->route('member.dashboard')->with('success', 'Berhasil login sebagai anggota!');
                }
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

    public function showRegister()
    {
        if (Auth::check()) {
            if (Auth::user()->role === 'admin') {
                return redirect()->route('admin.dashboard');
            } else {
                return redirect()->route('member.dashboard');
            }
        } elseif (session()->has('member_id')) {
            return redirect()->route('member.dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email|unique:members,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Create User record in users table (role = member)
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'member',
        ]);

        // Generate unique member code (kode_anggota)
        $latestMember = Member::latest()->first();
        $latestId = $latestMember ? $latestMember->id + 1 : 1;
        $kodeAnggota = 'MBR' . str_pad($latestId, 3, '0', STR_PAD_LEFT);

        // Create matching Member record in members table
        $member = Member::create([
            'kode_anggota' => $kodeAnggota,
            'nama' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // Member model setPasswordAttribute mutator will hash it
            'telepon' => '-',
            'alamat' => '-',
            'role' => 'member',
        ]);

        // Log the user in on both guard and session
        Auth::login($user);
        session(['member_id' => $member->id, 'member' => $member]);
        $request->session()->regenerate();

        return redirect()->route('member.dashboard')->with('success', 'Akun berhasil didaftarkan!');
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
