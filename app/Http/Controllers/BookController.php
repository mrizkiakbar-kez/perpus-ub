<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $categoryId = $request->get('category');

        $books = Book::with('category');

        if ($q) {
            $books->where(function($builder) use ($q) {
                $builder->where('judul', 'like', "%{$q}%")
                        ->orWhere('penulis', 'like', "%{$q}%")
                        ->orWhere('penerbit', 'like', "%{$q}%");
            });
        }

        if ($categoryId) {
            $books->where('category_id', $categoryId);
        }

        $books = $books->latest()->get();
        $categories = Category::orderBy('name')->get();

        return view('books.index', compact('books', 'q', 'categories', 'categoryId'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('books.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_buku' => 'required|unique:books',
            'judul' => 'required',
            'category_id' => 'nullable|exists:categories,id',
            'penulis' => 'required',
            'penerbit' => 'required',
            'tahun_terbit' => 'required|numeric',
            'stok' => 'required|numeric',
            'deskripsi' => 'nullable|string',
        ]);

        Book::create([
            'kode_buku' => $request->kode_buku,
            'judul' => $request->judul,
            'category_id' => $request->category_id,
            'penulis' => $request->penulis,
            'penerbit' => $request->penerbit,
            'tahun_terbit' => $request->tahun_terbit,
            'stok' => $request->stok,
            'deskripsi' => $request->deskripsi,
        ]);

        $route = Auth::check() && Auth::user()->role === 'admin' ? 'admin.books.index' : 'books.index';

        return redirect()
            ->route($route)
            ->with('success', 'Data buku berhasil ditambahkan.');
    }

    public function show(Book $book)
    {
        if (!(Auth::check() && Auth::user()->role === 'admin')) {
            return redirect()->route('books.index')->with('error', 'Unauthorized access.');
        }

        $book->load('category');
        
        $availableStock = $book->stok;
        $activeBorrowingsCount = $book->borrowings()->whereIn('status', ['borrowed', 'late'])->count();
        $totalStock = $book->stok + $activeBorrowingsCount;
        $borrowCount = $book->borrowings()->count();

        return view('books.show', compact('book', 'availableStock', 'totalStock', 'borrowCount'));
    }

    public function edit(Book $book)
    {
        $categories = Category::orderBy('name')->get();
        return view('books.edit', compact('book', 'categories'));
    }

    public function update(Request $request, Book $book)
    {
        $request->validate([
            'kode_buku' => 'required|unique:books,kode_buku,' . $book->id,
            'judul' => 'required',
            'category_id' => 'nullable|exists:categories,id',
            'penulis' => 'required',
            'penerbit' => 'required',
            'tahun_terbit' => 'required|numeric',
            'stok' => 'required|numeric',
            'deskripsi' => 'nullable|string',
        ]);

        $book->update($request->all());

        $route = Auth::check() && Auth::user()->role === 'admin' ? 'admin.books.index' : 'books.index';

        return redirect()
            ->route($route)
            ->with('success', 'Data buku berhasil diperbarui.');
    }

    public function destroy(Book $book)
    {
        $book->delete();

        $route = Auth::check() && Auth::user()->role === 'admin' ? 'admin.books.index' : 'books.index';

        return redirect()
            ->route($route)
            ->with('success', 'Data buku berhasil dihapus.');
    }
}