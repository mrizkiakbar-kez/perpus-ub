<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Member;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function admin()
    {
        $totalBooks = Book::count();
        $totalMembers = Member::count();
        $activeBorrowings = Borrowing::where('status', 'Dipinjam')->count();

        $recent = Borrowing::with('member')->latest()->limit(10)->get();

        return view('dashboard.admin', compact('totalBooks', 'totalMembers', 'activeBorrowings', 'recent'));
    }

    public function member()
    {
        $memberId = session('member_id');
        $member = session('member');

        if (!$memberId) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // Total borrowings
        $totalBorrowed = Borrowing::where('member_id', $memberId)->count();

        // Active borrowings
        $activeBorrowings = Borrowing::where('member_id', $memberId)->where('status', 'Dipinjam')->count();
        
        // Overdue borrowings
        $overdueBorrowings = Borrowing::where('member_id', $memberId)
            ->where('status', 'Dipinjam')
            ->where('return_date', '<', now()->toDateString())
            ->count();

        // Recent borrowing history
        $recent = Borrowing::where('member_id', $memberId)
            ->with('borrowingDetails.book')
            ->latest()
            ->limit(10)
            ->get();

        return view('dashboard.member', compact('totalBorrowed', 'activeBorrowings', 'overdueBorrowings', 'recent', 'member'));
    }
}
