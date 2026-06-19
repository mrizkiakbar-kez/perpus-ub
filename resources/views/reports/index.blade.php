@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>Laporan & Analitik</h2>
        <p class="text-muted mb-0">Tinjau seluruh riwayat peminjaman dan kelola keterlambatan pengembalian.</p>
    </div>
    <button onclick="window.print()" class="btn btn-outline-secondary d-print-none">
        <i class="bi bi-printer"></i> Cetak Laporan
    </button>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-2">Total Transaksi</p>
                        <h3>{{ $totalBorrowings ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-tags-fill" style="font-size: 32px; color: var(--primary-blue); opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card text-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-2">Sedang Dipinjam</p>
                        <h3>{{ $activeBorrowings ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-hourglass-split" style="font-size: 32px; color: var(--accent-warning); opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-{{ $overdueCount > 0 ? 'danger' : 'color' }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-2">Terlambat</p>
                        <h3 class="{{ $overdueCount > 0 ? 'text-danger' : '' }}">{{ $overdueCount ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-exclamation-octagon" style="font-size: 32px; color: {{ $overdueCount > 0 ? 'var(--accent-danger)' : 'var(--text-muted)' }}; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4 d-print-none">
    <div class="card-header bg-transparent border-bottom" style="border-color: var(--border-color) !important;">
        <ul class="nav nav-tabs card-header-tabs" id="reportTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active text-white" id="all-history-tab" data-bs-toggle="tab" data-bs-target="#all-history" type="button" role="tab" aria-controls="all-history" aria-selected="true">Semua Riwayat</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link text-white position-relative" id="overdue-tab" data-bs-toggle="tab" data-bs-target="#overdue" type="button" role="tab" aria-controls="overdue" aria-selected="false">
                    Daftar Terlambat
                    @if($overdueCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 8px;">
                            {{ $overdueCount }}
                        </span>
                    @endif
                </button>
            </li>
        </ul>
    </div>
    
    <div class="card-body tab-content" id="reportTabsContent">
        <!-- ALL HISTORY TAB -->
        <div class="tab-pane fade show active" id="all-history" role="tabpanel" aria-labelledby="all-history-tab">
            @if($history->count())
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0" style="--bs-table-bg: transparent; --bs-table-border-color: var(--border-color); vertical-align: middle;">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Anggota</th>
                                <th>Buku</th>
                                <th>Tgl Pinjam</th>
                                <th>Batas Kembali</th>
                                <th>Tgl Pengembalian</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($history as $h)
                                <tr>
                                    <td>#{{ $h->id }}</td>
                                    <td>
                                        <div><strong>{{ $h->member->nama }}</strong></div>
                                        <small class="text-muted">{{ $h->member->kode_anggota }}</small>
                                    </td>
                                    <td>
                                        @foreach($h->borrowingDetails as $detail)
                                            <div>{{ $detail->book->judul }} <span class="text-muted">({{ $detail->qty }})</span></div>
                                        @endforeach
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($h->borrow_date)->format('d M Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($h->return_date)->format('d M Y') }}</td>
                                    <td>
                                        @if($h->returned_at)
                                            {{ \Carbon\Carbon::parse($h->returned_at)->format('d M Y H:i') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $h->status === 'Dipinjam' ? 'bg-warning text-dark' : 'bg-success' }}">
                                            {{ $h->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted mb-0">Belum ada riwayat peminjaman.</p>
            @endif
        </div>

        <!-- OVERDUE TAB -->
        <div class="tab-pane fade" id="overdue" role="tabpanel" aria-labelledby="overdue-tab">
            @if($overdueBorrowings->count())
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0" style="--bs-table-bg: transparent; --bs-table-border-color: var(--border-color); vertical-align: middle;">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Anggota</th>
                                <th>Buku</th>
                                <th>Batas Kembali</th>
                                <th>Keterlambatan</th>
                                <th>Estimasi Denda</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($overdueBorrowings as $ob)
                                <tr>
                                    <td>#{{ $ob->id }}</td>
                                    <td>
                                        <div><strong>{{ $ob->member->nama }}</strong></div>
                                        <small class="text-muted">{{ $ob->member->telepon }}</small>
                                    </td>
                                    <td>
                                        @foreach($ob->borrowingDetails as $detail)
                                            <div>{{ $detail->book->judul }}</div>
                                        @endforeach
                                    </td>
                                    <td class="text-danger">{{ \Carbon\Carbon::parse($ob->return_date)->format('d M Y') }}</td>
                                    <td>
                                        <span class="badge bg-danger">{{ $ob->daysLate() }} Hari</span>
                                    </td>
                                    <td class="fw-bold text-danger">Rp {{ number_format($ob->calculatePenalty()) }}</td>
                                    <td>
                                        <a href="{{ route('admin.borrowings.show', $ob->id) }}" class="btn btn-sm btn-outline-primary">
                                            Proses
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state py-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 32px; opacity: 0.8; margin-bottom: 10px;"></i>
                    <h5>Tidak ada keterlambatan</h5>
                    <p class="text-muted mb-0">Seluruh peminjaman masih dalam rentang waktu pengembalian yang valid.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- PRINT ONLY VIEW -->
<div class="d-none d-print-block">
    <h4 class="mb-3">Daftar Peminjaman Buku</h4>
    <table class="table table-bordered table-sm text-dark" style="color: black !important;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Anggota</th>
                <th>Buku</th>
                <th>Tgl Pinjam</th>
                <th>Batas Kembali</th>
                <th>Tgl Kembali</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($history as $h)
                <tr>
                    <td>#{{ $h->id }}</td>
                    <td>{{ $h->member->nama }} ({{ $h->member->kode_anggota }})</td>
                    <td>
                        @foreach($h->borrowingDetails as $detail)
                            {{ $detail->book->judul }} ({{ $detail->qty }}),
                        @endforeach
                    </td>
                    <td>{{ $h->borrow_date }}</td>
                    <td>{{ $h->return_date }}</td>
                    <td>{{ $h->returned_at ?? '-' }}</td>
                    <td>{{ $h->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<style>
    @media print {
        body {
            background: white !important;
            color: black !important;
        }
        .card, .table {
            background: white !important;
            color: black !important;
            border-color: #ccc !important;
        }
        .main-wrapper, .sidebar, .navbar-section, .d-print-none {
            display: none !important;
        }
        .page-content {
            padding: 0 !important;
        }
    }
</style>

@endsection
