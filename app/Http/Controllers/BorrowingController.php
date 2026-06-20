<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Member;

class BorrowingController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');

        if (Auth::check() && Auth::user()->role === 'admin') {
            $query = Borrowing::with(['user', 'book']);
            
            // Search borrowing records
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->whereHas('user', function($u) use ($search) {
                        $u->where('name', 'like', '%' . $search . '%');
                    })->orWhereHas('book', function($b) use ($search) {
                        $b->where('judul', 'like', '%' . $search . '%');
                    });
                });
            }

            // Filter by Member Name
            if ($request->filled('member_name')) {
                $memberName = $request->get('member_name');
                $query->whereHas('user', function($q) use ($memberName) {
                    $q->where('name', 'like', '%' . $memberName . '%');
                });
            }

            // Filter by Book Title
            if ($request->filled('book_title')) {
                $bookTitle = $request->get('book_title');
                $query->whereHas('book', function($q) use ($bookTitle) {
                    $q->where('judul', 'like', '%' . $bookTitle . '%');
                });
            }

            // Filter by Status: Borrowed, Returned, Late
            $status = $request->get('status', $filter);
            if ($status === 'borrowed') {
                $query->where('status', 'borrowed')
                      ->where('due_date', '>=', now()->toDateString());
            } elseif ($status === 'returned') {
                $query->where('status', 'returned');
            } elseif ($status === 'late') {
                $query->where(function($q) {
                    $q->where('status', 'late')
                      ->orWhere(function($sub) {
                          $sub->where('status', 'borrowed')
                              ->where('due_date', '<', now()->toDateString());
                      });
                });
            }

            // Sort records
            $sortBy = $request->get('sort_by', 'borrow_date');
            $sortOrder = $request->get('sort_order', 'desc');
            if (in_array($sortBy, ['borrow_date', 'due_date']) && in_array($sortOrder, ['asc', 'desc'])) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('borrow_date', 'desc');
            }

            $borrowings = $query->paginate(20)->withQueryString();
        } else {
            // Get current user ID (Member)
            $userId = Auth::id();
            if (!$userId && session()->has('member_id')) {
                $member = Member::find(session('member_id'));
                $user = User::where('email', $member->email)->first();
                $userId = $user?->id;
            }

            if (!$userId) {
                return redirect()->route('login');
            }

            $query = Borrowing::where('user_id', $userId)->with('book')->latest();
            
            if ($filter === 'borrowed') {
                $query->where('status', 'borrowed');
            } elseif ($filter === 'returned') {
                $query->where('status', 'returned');
            } elseif ($filter === 'late') {
                $query->where('status', 'late');
            }
            
            $borrowings = $query->paginate(20)->withQueryString();
        }

        return view('borrowings.index', compact('borrowings', 'filter'));
    }

    public function create()
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            abort(403, 'Aksi tidak diperbolehkan untuk Admin.');
        }

        return redirect()->route('books.index')->with('error', 'Silakan pilih buku yang ingin dipinjam dari katalog.');
    }

    public function store(Request $request)
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            abort(403, 'Aksi tidak diperbolehkan untuk Admin.');
        }

        return $this->borrowDirect($request, $request->input('book_id'));
    }

    public function borrowDirect(Request $request, $bookId)
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            abort(403, 'Aksi tidak diperbolehkan untuk Admin.');
        }

        $userId = Auth::id();
        if (!$userId && session()->has('member_id')) {
            $member = Member::find(session('member_id'));
            $user = User::where('email', $member->email)->first();
            $userId = $user?->id;
        }

        if (!$userId) {
            return redirect()->route('login')->with('error', 'Anda harus login sebagai anggota untuk meminjam buku.');
        }

        $duration = (int) $request->input('duration', 7);
        if (!in_array($duration, [3, 7, 14, 30])) {
            $duration = 7;
        }

        // Prevent borrowing duplicate copy of the same book if already borrowed and not returned
        $alreadyBorrowed = Borrowing::where('user_id', $userId)
            ->where('book_id', $bookId)
            ->whereIn('status', ['borrowed', 'late'])
            ->exists();

        if ($alreadyBorrowed) {
            return back()->with('error', 'Anda sudah meminjam buku ini dan belum mengembalikannya.');
        }

        DB::beginTransaction();
        try {
            $book = Book::where('id', $bookId)->lockForUpdate()->first();
            if (!$book) {
                DB::rollBack();
                return back()->with('error', 'Buku tidak ditemukan.');
            }

            if ($book->stok <= 0) {
                DB::rollBack();
                return back()->with('error', 'Stok buku ini kosong.');
            }

            Borrowing::create([
                'user_id' => $userId,
                'book_id' => $bookId,
                'borrow_date' => now()->toDateString(),
                'due_date' => now()->addDays($duration)->toDateString(),
                'status' => 'borrowed',
                'duration_days' => $duration,
            ]);

            $book->decrement('stok', 1);

            DB::commit();

            return redirect()->route('borrowings.index')->with('success', 'Your book has been borrowed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat memproses peminjaman: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $borrowing = Borrowing::with(['user', 'book'])->findOrFail($id);

        // Security check for members
        if (!(Auth::check() && Auth::user()->role === 'admin')) {
            $userId = Auth::id();
            if (!$userId && session()->has('member_id')) {
                $member = Member::find(session('member_id'));
                $user = User::where('email', $member->email)->first();
                $userId = $user?->id;
            }
            if ($borrowing->user_id !== $userId) {
                abort(403, 'Aksi tidak diperbolehkan.');
            }
        }

        return view('borrowings.show', compact('borrowing'));
    }

    public function returnBook(Request $request, $id)
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            abort(403, 'Aksi tidak diperbolehkan untuk Admin.');
        }

        $borrowing = Borrowing::findOrFail($id);

        // Security check: members can only return their own books
        $userId = Auth::id();
        if (!$userId && session()->has('member_id')) {
            $member = Member::find(session('member_id'));
            $user = User::where('email', $member->email)->first();
            $userId = $user?->id;
        }
        if ($borrowing->user_id !== $userId) {
            abort(403, 'Aksi tidak diperbolehkan.');
        }

        if ($borrowing->status === 'returned' || $borrowing->status === 'late') {
            return redirect()->back()->with('info', 'Buku sudah dikembalikan sebelumnya.');
        }

        DB::beginTransaction();
        try {
            $book = Book::where('id', $borrowing->book_id)->lockForUpdate()->firstOrFail();

            $today = now()->startOfDay();
            $due = \Carbon\Carbon::parse($borrowing->due_date)->startOfDay();

            $isLate = $today->gt($due);
            $daysLate = $isLate ? abs((int) $today->diffInDays($due)) : 0;
            $penalty = $daysLate * 1000;

            $borrowing->return_date = now()->toDateString();
            $borrowing->status = $isLate ? 'late' : 'returned';
            $borrowing->late_penalty = $penalty;
            $borrowing->save();

            // Increment stock
            $book->increment('stok', 1);

            DB::commit();

            return redirect()->route('borrowings.index')->with('success', 'Buku berhasil dikembalikan.' . ($penalty > 0 ? " Denda terlambat: Rp " . number_format($penalty) : ""));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses pengembalian: ' . $e->getMessage());
        }
    }
}
