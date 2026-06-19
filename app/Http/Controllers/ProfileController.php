<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Member;

class ProfileController extends Controller
{
    public function edit()
    {
        $memberId = session('member_id');
        if (!$memberId && Auth::check() && Auth::user()->role === 'member') {
            $member = Member::where('email', Auth::user()->email)->first();
            $memberId = $member?->id;
        }

        if (!$memberId) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $member = Member::findOrFail($memberId);
        $user = User::where('email', $member->email)->first();

        return view('profile.edit', compact('member', 'user'));
    }

    public function update(Request $request)
    {
        $memberId = session('member_id');
        if (!$memberId && Auth::check() && Auth::user()->role === 'member') {
            $member = Member::where('email', Auth::user()->email)->first();
            $memberId = $member?->id;
        }

        if (!$memberId) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $member = Member::findOrFail($memberId);
        $user = User::where('email', $member->email)->first();

        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $name = $request->input('name');
        $password = $request->input('password');

        // Update Member
        $member->nama = $name;
        if ($password) {
            $member->password = $password; // sets through setPasswordAttribute mutator
        }
        $member->save();

        // Update User
        if ($user) {
            $user->name = $name;
            if ($password) {
                $user->password = Hash::make($password);
            }
            $user->save();
        }

        // Keep session synchronized
        session(['member' => $member]);

        return redirect()->route('member.profile')->with('success', 'Profil Anda berhasil diperbarui.');
    }
}
