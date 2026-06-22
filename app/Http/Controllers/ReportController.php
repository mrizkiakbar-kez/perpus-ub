<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    private function buildQuery(Request $request)
    {
        $query = Borrowing::with(['user', 'book']);

        // Date Preset Filters
        $filterType = $request->get('filter_type', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if ($filterType === 'today') {
            $query->whereDate('borrow_date', now()->toDateString());
        } elseif ($filterType === 'week') {
            $query->whereBetween('borrow_date', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()]);
        } elseif ($filterType === 'month') {
            $query->whereBetween('borrow_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()]);
        } elseif ($filterType === 'year') {
            $query->whereBetween('borrow_date', [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()]);
        } elseif ($filterType === 'custom') {
            if ($startDate && $endDate) {
                $query->whereBetween('borrow_date', [$startDate, $endDate]);
            } elseif ($startDate) {
                $query->where('borrow_date', '>=', $startDate);
            } elseif ($endDate) {
                $query->where('borrow_date', '<=', $endDate);
            }
        }

        // Search Filter (Book title, member name)
        $search = $request->get('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('book', function($qb) use ($search) {
                    $qb->where('judul', 'like', "%{$search}%");
                })->orWhereHas('user', function($qu) use ($search) {
                    $qu->where('name', 'like', "%{$search}%");
                });
            });
        }

        return $query;
    }

    private function getStats($query)
    {
        $totalBorrowed = (clone $query)->count();
        
        $totalReturned = (clone $query)->whereIn('status', ['returned', 'late'])->count();
        
        $totalCurrentlyBorrowed = (clone $query)->where('status', 'borrowed')->count();
        
        $totalOverdue = (clone $query)->where('status', 'borrowed')
            ->where('due_date', '<', now()->toDateString())
            ->count();
            
        $totalMembersBorrowing = (clone $query)->distinct('user_id')->count('user_id');

        return [
            'totalBorrowed' => $totalBorrowed,
            'totalReturned' => $totalReturned,
            'totalCurrentlyBorrowed' => $totalCurrentlyBorrowed,
            'totalOverdue' => $totalOverdue,
            'totalMembersBorrowing' => $totalMembersBorrowing,
        ];
    }

    private function getDateRangeText(Request $request)
    {
        $filterType = $request->get('filter_type', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if ($filterType === 'today') {
            return now()->translatedFormat('d M Y');
        } elseif ($filterType === 'week') {
            return now()->startOfWeek()->translatedFormat('d M Y') . ' - ' . now()->endOfWeek()->translatedFormat('d M Y');
        } elseif ($filterType === 'month') {
            return now()->startOfMonth()->translatedFormat('d M Y') . ' - ' . now()->endOfMonth()->translatedFormat('d M Y');
        } elseif ($filterType === 'year') {
            return now()->startOfYear()->translatedFormat('d M Y') . ' - ' . now()->endOfYear()->translatedFormat('d M Y');
        } elseif ($filterType === 'custom') {
            return ($startDate ? \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') : 'Start') . ' - ' . ($endDate ? \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') : 'End');
        }

        return 'All Time';
    }

    public function index(Request $request)
    {
        $query = $this->buildQuery($request);
        $stats = $this->getStats($query);
        
        $history = (clone $query)->latest()->paginate(15)->withQueryString();
        
        $overdueBorrowings = (clone $query)->where('status', 'borrowed')
            ->where('due_date', '<', now()->toDateString())
            ->latest()
            ->get();
            
        $overdueCount = $overdueBorrowings->count();

        return view('reports.index', array_merge($stats, [
            'history' => $history,
            'overdueBorrowings' => $overdueBorrowings,
            'overdueCount' => $overdueCount,
        ]));
    }

    public function print(Request $request)
    {
        $query = $this->buildQuery($request);
        $records = $query->latest()->get();
        $dateRange = $this->getDateRangeText($request);
        $generatedBy = Auth::user()->name;

        return view('reports.print', [
            'records' => $records,
            'dateRange' => $dateRange,
            'generatedBy' => $generatedBy,
        ]);
    }

    public function pdf(Request $request)
    {
        $query = $this->buildQuery($request);
        $records = $query->latest()->get();
        $dateRange = $this->getDateRangeText($request);
        $generatedBy = Auth::user()->name;

        $pdf = Pdf::loadView('reports.pdf', [
            'records' => $records,
            'dateRange' => $dateRange,
            'generatedBy' => $generatedBy,
        ]);

        return $pdf->download('Borrowing_Report_' . now()->toDateString() . '.pdf');
    }
}
