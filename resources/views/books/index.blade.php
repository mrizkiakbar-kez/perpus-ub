@extends('layouts.app')

@section('content')

<style>
    /* Genre Filter Pills (MangaDex inspired) */
    .genre-filter-container {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 24px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--border-color);
    }

    .genre-pill {
        display: inline-block;
        padding: 6px 14px;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        color: var(--text-secondary);
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .genre-pill:hover,
    .genre-pill.active {
        background: rgba(59, 130, 246, 0.15);
        border-color: var(--primary-blue);
        color: var(--primary-blue);
        box-shadow: 0 0 10px rgba(59, 130, 246, 0.1);
    }

    /* Manga Cover Card Layout */
    .manga-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: 100%;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    .manga-card:hover {
        transform: translateY(-5px);
        border-color: var(--primary-blue);
        box-shadow: 0 8px 24px rgba(59, 130, 246, 0.2);
    }

    .manga-cover {
        position: relative;
        height: 200px;
        display: flex;
        align-items: flex-end;
        padding: 16px;
        overflow: hidden;
    }

    /* Gradient Cover Variations */
    .cover-grad-1 { background: linear-gradient(135deg, #1e1b4b 0%, #311042 50%, #4c1d95 100%); }
    .cover-grad-2 { background: linear-gradient(135deg, #064e3b 0%, #022c22 50%, #115e59 100%); }
    .cover-grad-3 { background: linear-gradient(135deg, #0c4a6e 0%, #0f172a 50%, #0369a1 100%); }
    .cover-grad-4 { background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #d97706 100%); }
    .cover-grad-5 { background: linear-gradient(135deg, #701a75 0%, #4a044e 50%, #be185d 100%); }

    /* Overlay details */
    .manga-cover-pattern {
        position: absolute;
        inset: 0;
        background-image: radial-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 0);
        background-size: 8px 8px;
        opacity: 0.8;
    }

    .manga-cover-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(15, 23, 42, 0.9) 0%, rgba(15, 23, 42, 0.4) 50%, rgba(15, 23, 42, 0.1) 100%);
        z-index: 1;
    }

    .manga-title-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        z-index: 10;
        font-size: 10px;
        background: rgba(59, 130, 246, 0.2);
        border: 1px solid var(--primary-blue);
        color: var(--primary-blue);
        padding: 2px 6px;
        border-radius: 4px;
        font-weight: 600;
    }

    .manga-code-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        z-index: 10;
        font-size: 10px;
        background: rgba(15, 23, 42, 0.7);
        color: var(--text-muted);
        padding: 2px 6px;
        border-radius: 4px;
        font-family: monospace;
    }

    .manga-cover-title {
        color: white;
        font-size: 17px;
        font-weight: 700;
        line-height: 1.3;
        margin: 0;
        text-shadow: 0 2px 4px rgba(0,0,0,0.8);
        z-index: 2;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .manga-details {
        padding: 16px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .manga-meta {
        font-size: 12px;
        color: var(--text-secondary);
        margin-bottom: 6px;
    }

    .manga-meta i {
        color: var(--primary-blue);
        margin-right: 6px;
        font-size: 14px;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>Katalog Buku</h2>
        @if(!empty($q))
            <p class="text-muted mb-0">Hasil pencarian untuk "<strong>{{ $q }}</strong>"</p>
        @endif
    </div>
    @if(Auth::check() && Auth::user()->role === 'admin')
        <a href="{{ route('admin.books.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Tambah Buku
        </a>
    @endif
</div>

<!-- Category Tag Pills -->
<div class="genre-filter-container">
    @php
        $indexRoute = Auth::check() && Auth::user()->role === 'admin' ? 'admin.books.index' : 'books.index';
    @endphp
    <a href="{{ route($indexRoute, ['q' => $q]) }}" class="genre-pill {{ empty($categoryId) ? 'active' : '' }}">
        Semua Kategori
    </a>
    @foreach($categories as $category)
        <a href="{{ route($indexRoute, ['category' => $category->id, 'q' => $q]) }}" 
           class="genre-pill {{ $categoryId == $category->id ? 'active' : '' }}">
            {{ $category->name }}
        </a>
    @endforeach
</div>

@if($books->count())
    <div class="row g-4">
        @foreach($books as $book)
            @php
                // Cycle through 5 gradient covers based on book ID
                $gradNum = ($book->id % 5) + 1;
            @endphp
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="manga-card">
                    
                    <!-- Cover image/gradient container -->
                    <div class="manga-cover cover-grad-{{ $gradNum }}">
                        <div class="manga-cover-pattern"></div>
                        <div class="manga-cover-overlay"></div>
                        <div class="manga-code-badge">{{ $book->kode_buku }}</div>
                        <div class="manga-title-badge">{{ $book->category->name ?? 'Manga' }}</div>
                        <h4 class="manga-cover-title">{{ $book->judul }}</h4>
                    </div>

                    <!-- Details and actions -->
                    <div class="manga-details d-flex flex-column">
                        <div class="manga-meta mb-2">
                            <i class="bi bi-person"></i>{{ $book->penulis }}
                        </div>
                        <div class="manga-meta mb-3">
                            <i class="bi bi-building"></i>{{ $book->penerbit }}
                        </div>
                        
                        <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center" style="border-color: var(--border-color) !important;">
                            <span class="badge {{ $book->stok > 0 ? 'bg-success' : 'bg-danger' }}">
                                Stok: {{ $book->stok }}
                            </span>
                            
                            <div class="d-flex gap-2">
                                @if(Auth::check() && Auth::user()->role === 'admin')
                                    <a href="{{ route('admin.books.show', $book->id) }}" class="btn btn-sm btn-primary" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.books.edit', $book->id) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.books.destroy', $book->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus buku ini?')" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @else
                                    @if(session()->has('member_id') || (Auth::check() && Auth::user()->role === 'member'))
                                        @if($book->stok > 0)
                                            <form action="{{ route('books.borrow', $book->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="bi bi-journal-plus"></i> Pinjam
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-sm btn-secondary" disabled title="Stok Habis">
                                                <i class="bi bi-x-circle"></i> Habis
                                            </button>
                                        @endif
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="card">
        @if(!empty($q) || !empty($categoryId))
            <div class="empty-state">
                <i class="bi bi-search"></i>
                <h5>Tidak ditemukan</h5>
                <p class="text-muted mb-3">Tidak ada buku yang cocok dengan filter atau pencarian Anda.</p>
                <a href="{{ route($indexRoute) }}" class="btn btn-primary btn-sm">Lihat Semua Buku</a>
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-book"></i>
                <h5>Belum ada buku</h5>
                <p class="text-muted mb-3">Koleksi perpustakaan saat ini masih kosong.</p>
                @if(Auth::check() && Auth::user()->role === 'admin')
                    <a href="{{ route('admin.books.create') }}" class="btn btn-primary btn-sm">Tambah Buku Baru</a>
                @endif
            </div>
        @endif
    </div>
@endif

@endsection