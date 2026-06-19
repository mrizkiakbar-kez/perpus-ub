<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\BorrowingDetail;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BorrowingController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            $borrowings = Borrowing::with(['member', 'borrowingDetails.book'])->latest()->paginate(20);
        } elseif (session()->has('member_id')) {
            $memberId = session('member_id');
            $borrowings = Borrowing::where('member_id', $memberId)
                ->with('borrowingDetails.book')
                ->latest()
                ->paginate(20);
        } else {
            return redirect()->route('login');
        }

        return view('borrowings.index', compact('borrowings'));
    }

    public function create()
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            $members = Member::orderBy('nama')->get();
            $books = Book::where('stok', '>', 0)->orderBy('judul')->get();
            return view('borrowings.create', compact('members', 'books'));
        }

        return redirect()->route('books.index')->with('error', 'Silakan pilih buku yang ingin dipinjam dari katalog.');
    }

    public function store(Request $request)
    {
        // If member is logged in, automatically inject their member_id
        if (!Auth::check() && session()->has('member_id')) {
            $request->merge(['member_id' => session('member_id')]);
        }

        $data = $request->validate([
            'member_id' => 'required|exists:members,id',
            'return_date' => 'required|date|after_or_equal:today',
            'items' => 'required|array|min:1',
            'items.*.book_id' => 'required|exists:books,id',
            'items.*.qty' => 'required|integer|min:1'
        ]);

        DB::beginTransaction();

        try {
            // create borrowing
            $borrowing = Borrowing::create([
                'member_id' => $data['member_id'],
                'borrow_date' => now()->toDateString(),
                'return_date' => $data['return_date'],
                'status' => 'Dipinjam',
                'user_id' => Auth::check() ? Auth::user()->id : null,
            ]);

            // process items
            foreach ($data['items'] as $item) {
                $book = Book::findOrFail($item['book_id']);
                $qty = (int) $item['qty'];

                if (! $book->isAvailable($qty)) {
                    DB::rollBack();
                    return back()->withInput()->withErrors(['stock' => "Buku {$book->judul} tidak memiliki stok yang cukup."]); 
                }

                // create detail
                BorrowingDetail::create([
                    'borrowing_id' => $borrowing->id,
                    'book_id' => $book->id,
                    'qty' => $qty,
                ]);

                // decrement stock
                $book->decrement('stok', $qty);
            }

            DB::commit();

            $route = Auth::check() && Auth::user()->role === 'admin' ? 'admin.borrowings.index' : 'borrowings.index';
            return redirect()->route($route)->with('success', 'Peminjaman berhasil dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $borrowing = Borrowing::with(['member', 'borrowingDetails.book'])->findOrFail($id);

        // Security check for members
        if (!Auth::check() && session()->has('member_id')) {
            if ($borrowing->member_id !== session('member_id')) {
                abort(403, 'Aksi tidak diperbolehkan.');
            }
        }

        return view('borrowings.show', compact('borrowing'));
    }

    public function processReturn(Request $request, $id)
    {
        $borrowing = Borrowing::with('borrowingDetails.book')->findOrFail($id);

        // Security check for members
        if (!Auth::check() && session()->has('member_id')) {
            if ($borrowing->member_id !== session('member_id')) {
                abort(403, 'Aksi tidak diperbolehkan.');
            }
        }

        if ($borrowing->status === 'Dikembalikan') {
            return redirect()->back()->with('info', 'Buku sudah dikembalikan sebelumnya.');
        }

        DB::beginTransaction();
        try {
            // increment stock for each book
            foreach ($borrowing->borrowingDetails as $detail) {
                $book = $detail->book;
                $book->increment('stok', $detail->qty);
            }

            $borrowing->status = 'Dikembalikan';
            $borrowing->returned_at = now();
            $borrowing->returned_by = Auth::check() ? Auth::user()->id : null;
            $borrowing->save();

            DB::commit();

            $route = Auth::check() && Auth::user()->role === 'admin' ? 'admin.borrowings.show' : 'borrowings.show';
            return redirect()->route($route, $borrowing->id)->with('success', 'Buku berhasil dikembalikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
