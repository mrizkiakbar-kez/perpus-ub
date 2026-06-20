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
            $query = Borrowing::with(['user', 'book'])->latest();
            
            if ($filter === 'borrowed') {
                $query->where('status', 'borrowed')
                      ->where('due_date', '>=', now()->toDateString());
            } elseif ($filter === 'returned') {
                $query->where('status', 'returned');
            } elseif ($filter === 'late') {
                $query->where('status', 'late')
                      ->orWhere(function($q) {
                          $q->where('status', 'borrowed')
                            ->where('due_date', '<', now()->toDateString());
                      });
            }
            
            $borrowings = $query->paginate(20);
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
            
            $borrowings = $query->paginate(20);
        }

        return view('borrowings.index', compact('borrowings', 'filter'));
    }

    public function create()
    {
        // Admin creates borrowings manually
        if (Auth::check() && Auth::user()->role === 'admin') {
            $members = Member::orderBy('nama')->get();
            $books = Book::where('stok', '>', 0)->orderBy('judul')->get();
            return view('borrowings.create', compact('members', 'books'));
        }

        return redirect()->route('books.index')->with('error', 'Silakan pilih buku yang ingin dipinjam dari katalog.');
    }

    public function store(Request $request)
    {
        // Handle admin manual creation fallback or redirect
        if (Auth::check() && Auth::user()->role === 'admin') {
            return $this->storeAdminBorrowing($request);
        }

        return $this->borrowDirect($request, $request->input('book_id'));
    }

    private function storeAdminBorrowing(Request $request)
    {
        $data = $request->validate([
            'member_id' => 'required|exists:members,id',
            'book_id' => 'required|exists:books,id',
            'duration' => 'required|integer|in:3,7,14,30'
        ]);

        $member = Member::findOrFail($data['member_id']);
        $user = User::where('email', $member->email)->first();
        if (!$user) {
            return back()->withInput()->withErrors(['member_id' => 'Akun user untuk member ini tidak ditemukan.']);
        }

        $userId = $user->id;
        $bookId = $data['book_id'];
        $duration = (int) $data['duration'];

        // Prevent borrowing duplicate copy
        $alreadyBorrowed = Borrowing::where('user_id', $userId)
            ->where('book_id', $bookId)
            ->whereIn('status', ['borrowed', 'late'])
            ->exists();

        if ($alreadyBorrowed) {
            return back()->withInput()->withErrors(['book_id' => 'Member ini sudah meminjam buku ini dan belum mengembalikannya.']);
        }

        DB::beginTransaction();
        try {
            $book = Book::where('id', $bookId)->lockForUpdate()->first();
            if (!$book || $book->stok <= 0) {
                DB::rollBack();
                return back()->withInput()->withErrors(['book_id' => 'Stok buku ini kosong.']);
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

            return redirect()->route('admin.borrowings.index')->with('success', 'Peminjaman berhasil dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function borrowDirect(Request $request, $bookId)
    {
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

            return redirect()->route('borrowings.index')->with('success', 'Buku berhasil dipinjam.');
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
        $borrowing = Borrowing::findOrFail($id);

        // Security check: members can only return their own books
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

        if ($borrowing->status === 'returned' || $borrowing->status === 'late') {
            return redirect()->back()->with('info', 'Buku sudah dikembalikan sebelumnya.');
        }

        DB::beginTransaction();
        try {
            $book = Book::where('id', $borrowing->book_id)->lockForUpdate()->firstOrFail();

            $today = now()->startOfDay();
            $due = \Carbon\Carbon::parse($borrowing->due_date)->startOfDay();

            $isLate = $today->gt($due);
            $daysLate = $isLate ? $today->diffInDays($due) : 0;
            $penalty = $daysLate * 1000;

            $borrowing->return_date = now()->toDateString();
            $borrowing->status = $isLate ? 'late' : 'returned';
            $borrowing->late_penalty = $penalty;
            $borrowing->save();

            // Increment stock
            $book->increment('stok', 1);

            DB::commit();

            $route = Auth::check() && Auth::user()->role === 'admin' ? 'admin.borrowings.index' : 'borrowings.index';
            return redirect()->route($route)->with('success', 'Buku berhasil dikembalikan.' . ($penalty > 0 ? " Denda terlambat: Rp " . number_format($penalty) : ""));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses pengembalian: ' . $e->getMessage());
        }
    }
}
