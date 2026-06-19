@extends('layouts.app')

@section('content')

<style>
    /* Book Detail Cover Style */
    .manga-detail-cover {
        position: relative;
        height: 280px;
        border-radius: 6px;
        overflow: hidden;
        display: flex;
        align-items: flex-end;
        padding: 20px;
        margin-bottom: 20px;
    }

    /* Gradient Cover Variations */
    .cover-grad-1 { background: linear-gradient(135deg, #1e1b4b 0%, #311042 50%, #4c1d95 100%); }
    .cover-grad-2 { background: linear-gradient(135deg, #064e3b 0%, #022c22 50%, #115e59 100%); }
    .cover-grad-3 { background: linear-gradient(135deg, #0c4a6e 0%, #0f172a 50%, #0369a1 100%); }
    .cover-grad-4 { background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #d97706 100%); }
    .cover-grad-5 { background: linear-gradient(135deg, #701a75 0%, #4a044e 50%, #be185d 100%); }

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
        background: linear-gradient(to top, rgba(15, 23, 42, 0.9) 0%, rgba(15, 23, 42, 0.4) 60%, rgba(15, 23, 42, 0.2) 100%);
        z-index: 1;
    }

    .manga-cover-title {
        color: white;
        font-size: 24px;
        font-weight: 800;
        line-height: 1.3;
        margin: 0;
        text-shadow: 0 2px 4px rgba(0,0,0,0.8);
        z-index: 2;
    }

    .manga-code-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        z-index: 10;
        font-size: 11px;
        background: rgba(15, 23, 42, 0.7);
        color: var(--text-muted);
        padding: 3px 8px;
        border-radius: 4px;
        font-family: monospace;
    }

    .manga-detail-meta i {
        color: var(--primary-blue);
        font-size: 16px;
    }
</style>

<div class="mb-4">
    <a href="{{ Auth::check() && Auth::user()->role === 'admin' ? route('admin.books.index') : route('books.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali ke Katalog
    </a>
</div>

@php
    $gradNum = ($book->id % 5) + 1;
@endphp

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4 overflow-hidden">
            <!-- Book Header Visual Cover -->
            <div class="manga-detail-cover cover-grad-{{ $gradNum }}">
                <div class="manga-cover-pattern"></div>
                <div class="manga-cover-overlay"></div>
                <div class="manga-code-badge">{{ $book->kode_buku }}</div>
                <h2 class="manga-cover-title">{{ $book->judul }}</h2>
            </div>

            <div class="card-body pt-0">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom" style="border-color: var(--border-color) !important;">
                    <div>
                        <span class="text-muted small">Kategori:</span>
                        <span class="badge bg-primary ms-1">{{ $book->category->name ?? 'Tanpa Kategori' }}</span>
                    </div>
                    <div>
                        <span class="badge {{ $book->stok > 0 ? 'bg-success' : 'bg-danger' }} p-2">
                            {{ $book->stok > 0 ? 'Tersedia' : 'Kosong' }}
                        </span>
                    </div>
                </div>

                <div class="row g-4 manga-detail-meta">
                    <div class="col-sm-6">
                        <p class="text-muted mb-1 small">Penulis</p>
                        <p class="fw-bold text-white h6"><i class="bi bi-person me-2"></i>{{ $book->penulis }}</p>
                    </div>
                    <div class="col-sm-6">
                        <p class="text-muted mb-1 small">Penerbit</p>
                        <p class="fw-bold text-white h6"><i class="bi bi-building me-2"></i>{{ $book->penerbit }}</p>
                    </div>
                    <div class="col-sm-6">
                        <p class="text-muted mb-1 small">Tahun Terbit</p>
                        <p class="fw-bold text-white h6"><i class="bi bi-calendar me-2"></i>{{ $book->tahun_terbit }}</p>
                    </div>
                    <div class="col-sm-6">
                        <p class="text-muted mb-1 small">Jumlah Stok</p>
                        <p class="fw-bold text-white h6"><i class="bi bi-layers me-2"></i>{{ $book->stok }} exemplar</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!Auth::check() && session()->has('member_id'))
        <div class="col-md-4">
            <div class="card border-primary">
                <div class="card-header bg-transparent py-3 text-white fw-bold" style="border-color: var(--border-color) !important;">
                    <i class="bi bi-journal-plus text-primary"></i> Pinjam Buku Ini
                </div>
                <div class="card-body">
                    @if($book->stok > 0)
                        <form action="{{ route('borrowings.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="member_id" value="{{ session('member_id') }}">
                            <input type="hidden" name="items[0][book_id]" value="{{ $book->id }}">
                            <input type="hidden" name="items[0][qty]" value="1">

                            <div class="mb-4">
                                <label class="form-label" for="return_date">Batas Pengembalian</label>
                                <input type="date" name="return_date" id="return_date" class="form-control @error('return_date') is-invalid @enderror" 
                                    value="{{ \Carbon\Carbon::today()->addDays(7)->toDateString() }}" 
                                    min="{{ \Carbon\Carbon::tomorrow()->toDateString() }}" required>
                                <div class="form-text text-muted small mt-1">Batas peminjaman default adalah 7 hari.</div>
                                @error('return_date')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2">
                                <i class="bi bi-journal-check"></i> Ajukan Peminjaman
                            </button>
                        </form>
                    @else
                        <div class="alert alert-danger mb-0">
                            <i class="bi bi-exclamation-triangle-fill"></i> Stok buku saat ini sedang kosong, sehingga tidak dapat dipinjam.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

@endsection
