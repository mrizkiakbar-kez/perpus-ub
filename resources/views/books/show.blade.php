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

<div class="mb-4 d-flex justify-content-between align-items-center">
    <a href="{{ route('admin.books.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali ke Katalog
    </a>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.books.edit', $book->id) }}" class="btn btn-warning btn-sm text-dark fw-bold">
            <i class="bi bi-pencil"></i> Edit Buku
        </a>
        <form action="{{ route('admin.books.destroy', $book->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus buku ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">
                <i class="bi bi-trash"></i> Hapus
            </button>
        </form>
    </div>
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
                <div class="manga-code-badge">ISBN / Kode: {{ $book->kode_buku }}</div>
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
                            {{ $book->stok > 0 ? 'Stok Tersedia' : 'Stok Kosong' }}
                        </span>
                    </div>
                </div>

                <div class="row g-4 manga-detail-meta mb-4">
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
                        <p class="text-muted mb-1 small">Kode Buku / ISBN</p>
                        <p class="fw-bold text-white h6"><i class="bi bi-upc-scan me-2"></i>{{ $book->kode_buku }}</p>
                    </div>
                </div>

                <div class="border-top pt-4" style="border-color: var(--border-color) !important;">
                    <h5 class="text-white mb-2">Deskripsi / Sinopsis</h5>
                    <p class="text-secondary" style="white-space: pre-line; line-height: 1.7;">
                        {{ $book->deskripsi ?? 'Sinopsis tidak tersedia untuk buku ini.' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Sidebar Statistics Card -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-transparent py-3 text-white fw-bold" style="border-color: var(--border-color) !important;">
                <i class="bi bi-bar-chart-fill text-primary"></i> Statistik Buku
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Total Stok</span>
                    <span class="badge bg-secondary text-white fs-6">{{ $totalStock }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Stok Tersedia (Available)</span>
                    <span class="badge bg-success text-white fs-6">{{ $availableStock }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Sedang Dipinjam</span>
                    <span class="badge bg-warning text-dark fs-6">{{ $totalStock - $availableStock }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Total Kali Dipinjam</span>
                    <span class="badge bg-info text-dark fs-6">{{ $borrowCount }}</span>
                </div>
                
                <div class="border-top mt-4 pt-3 text-muted small" style="border-color: var(--border-color) !important;">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Ditambahkan pada:</span>
                        <span>{{ $book->created_at ? $book->created_at->format('d M Y H:i') : '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Terakhir diperbarui:</span>
                        <span>{{ $book->updated_at ? $book->updated_at->format('d M Y H:i') : '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
