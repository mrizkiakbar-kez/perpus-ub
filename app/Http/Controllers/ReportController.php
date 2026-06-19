<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        // Total borrowings count by status
        $totalBorrowings = Borrowing::count();
        $activeBorrowings = Borrowing::where('status', 'Dipinjam')->count();
        $returnedBorrowings = Borrowing::where('status', 'Dikembalikan')->count();

        // Overdue borrowings (status is Dipinjam and return_date is past today)
        $overdueBorrowings = Borrowing::where('status', 'Dipinjam')
            ->where('return_date', '<', now()->toDateString())
            ->with(['member', 'borrowingDetails.book'])
            ->get();

        $overdueCount = $overdueBorrowings->count();

        // Complete history
        $history = Borrowing::with(['member', 'borrowingDetails.book', 'user'])
            ->latest()
            ->get();

        return view('reports.index', compact(
            'totalBorrowings',
            'activeBorrowings',
            'returnedBorrowings',
            'overdueBorrowings',
            'overdueCount',
            'history'
        ));
    }
}
