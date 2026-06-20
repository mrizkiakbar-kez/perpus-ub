<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

use App\Models\User;
use App\Models\Borrowing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{
    public function index()
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Aksi tidak diperbolehkan.');
        }

        $members = Member::latest()->get();

        foreach ($members as $member) {
            $user = User::where('email', $member->email)->first();
            $member->user_id = $user?->id;
            $member->user_role = $user?->role ?? $member->role;
            if ($user) {
                $member->active_borrowings_count = Borrowing::where('user_id', $user->id)
                    ->where('status', 'borrowed')
                    ->count();
                $member->overdue_borrowings_count = Borrowing::where('user_id', $user->id)
                    ->where('status', 'borrowed')
                    ->where('due_date', '<', now()->toDateString())
                    ->count();
            } else {
                $member->active_borrowings_count = 0;
                $member->overdue_borrowings_count = 0;
            }
        }

        return view('members.index', compact('members'));
    }

    public function create()
    {
        abort(403, 'Aksi tidak diperbolehkan.');
    }

    public function store(Request $request)
    {
        abort(403, 'Aksi tidak diperbolehkan.');
    }

    public function show(Member $member)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Aksi tidak diperbolehkan.');
        }

        $user = User::where('email', $member->email)->first();
        $borrowings = collect();
        if ($user) {
            $borrowings = Borrowing::where('user_id', $user->id)
                ->with('book')
                ->latest()
                ->get();
        }

        return view('members.show', compact('member', 'user', 'borrowings'));
    }

    public function edit(Member $member)
    {
        abort(403, 'Aksi tidak diperbolehkan.');
    }

    public function update(Request $request, Member $member)
    {
        abort(403, 'Aksi tidak diperbolehkan.');
    }

    public function destroy(Member $member)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Aksi tidak diperbolehkan.');
        }

        $user = User::where('email', $member->email)->first();

        // Prevent deleting the currently logged-in Admin.
        if ($user && $user->id === Auth::id()) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus Admin yang sedang login.');
        }

        // Prevent deleting another Admin account.
        if (($user && $user->role === 'admin') || $member->role === 'admin') {
            return redirect()->back()->with('error', 'Tidak dapat menghapus akun Admin.');
        }

        if ($user) {
            // Prevent deleting members who still have active/overdue borrowed books.
            $hasActiveOrOverdue = Borrowing::where('user_id', $user->id)
                ->where('status', 'borrowed')
                ->exists();

            if ($hasActiveOrOverdue) {
                return redirect()->back()->with('error', 'This member cannot be removed because they still have active borrowing records.');
            }
        }

        // Use database transactions to ensure data integrity.
        DB::beginTransaction();
        try {
            if ($user) {
                $user->delete();
            }
            $member->delete();
            DB::commit();

            return redirect()->route('admin.members.index')->with('success', 'Anggota berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus anggota: ' . $e->getMessage());
        }
    }
}