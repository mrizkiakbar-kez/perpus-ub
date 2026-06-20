<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function admin()
    {
        $totalBooks = Book::count();
        $totalMembers = Member::count();
        $activeBorrowings = Borrowing::where('status', 'borrowed')->count();

        $recent = Borrowing::with(['user', 'book'])->latest()->limit(10)->get();

        return view('dashboard.admin', compact('totalBooks', 'totalMembers', 'activeBorrowings', 'recent'));
    }

    public function member()
    {
        $userId = Auth::id();
        $member = session('member');

        if (!$userId && session()->has('member_id')) {
            $member = Member::find(session('member_id'));
            $user = User::where('email', $member->email)->first();
            $userId = $user?->id;
        }

        if (!$userId) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // Total borrowings
        $totalBorrowed = Borrowing::where('user_id', $userId)->count();

        // Active borrowings
        $activeBorrowings = Borrowing::where('user_id', $userId)->where('status', 'borrowed')->count();
        
        // Overdue borrowings
        $overdueBorrowings = Borrowing::where('user_id', $userId)
            ->where('status', 'borrowed')
            ->where('due_date', '<', now()->toDateString())
            ->count();

        // Recent borrowing history
        $recent = Borrowing::where('user_id', $userId)
            ->with('book')
            ->latest()
            ->limit(10)
            ->get();

        return view('dashboard.member', compact('totalBorrowed', 'activeBorrowings', 'overdueBorrowings', 'recent', 'member'));
    }
}
