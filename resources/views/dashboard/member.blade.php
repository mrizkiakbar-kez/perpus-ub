@extends('layouts.app')

@section('content')

<div class="mb-4">
    <h2>Halo, {{ $member->nama }}!</h2>
    <p class="text-muted">Selamat datang di portal anggota Perpustakaan UB.</p>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-2">Buku Pernah Dipinjam</p>
                        <h3>{{ $totalBorrowed ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-journal-bookmark-fill" style="font-size: 32px; color: var(--primary-blue); opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-2">Peminjaman Aktif</p>
                        <h3>{{ $activeBorrowings ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-clock-history" style="font-size: 32px; color: var(--accent-warning); opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-{{ $overdueBorrowings > 0 ? 'danger' : 'color' }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-2">Terlambat Dikembalikan</p>
                        <h3 class="{{ $overdueBorrowings > 0 ? 'text-danger' : '' }}">{{ $overdueBorrowings ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-exclamation-triangle-fill" style="font-size: 32px; color: {{ $overdueBorrowings > 0 ? 'var(--accent-danger)' : 'var(--text-muted)' }}; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="mb-3">Peminjaman Terakhir Saya</h5>
        @if($recent->count())
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0" style="--bs-table-bg: transparent; --bs-table-border-color: var(--border-color);">
                    <thead>
                        <tr>
                            <th>Buku</th>
                            <th>Tanggal Pinjam</th>
                            <th>Batas Pengembalian</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recent as $r)
                            <tr>
                                <td>
                                    @foreach($r->borrowingDetails as $detail)
                                        <div>{{ $detail->book->judul }} <span class="text-muted">({{ $detail->qty }} pcs)</span></div>
                                    @endforeach
                                </td>
                                <td>{{ \Carbon\Carbon::parse($r->borrow_date)->format('d M Y') }}</td>
                                <td>
                                    <span class="{{ $r->status === 'Dipinjam' && \Carbon\Carbon::parse($r->return_date)->isPast() ? 'text-danger fw-bold' : '' }}">
                                        {{ \Carbon\Carbon::parse($r->return_date)->format('d M Y') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $r->status === 'Dipinjam' ? 'bg-warning text-dark' : 'bg-success' }}">
                                        {{ $r->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted mb-0">Anda belum memiliki riwayat peminjaman.</p>
        @endif
    </div>
</div>

@endsection
