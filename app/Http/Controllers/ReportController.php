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
        $activeBorrowings = Borrowing::where('status', 'borrowed')->count();
        $returnedBorrowings = Borrowing::whereIn('status', ['returned', 'late'])->count();

        // Overdue borrowings (status is borrowed and due_date is past today)
        $overdueBorrowings = Borrowing::where('status', 'borrowed')
            ->where('due_date', '<', now()->toDateString())
            ->with(['user', 'book'])
            ->get();

        $overdueCount = $overdueBorrowings->count();

        // Complete history
        $history = Borrowing::with(['user', 'book'])
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
