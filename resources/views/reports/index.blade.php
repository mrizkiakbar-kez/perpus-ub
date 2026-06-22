@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>Laporan & Analitik</h2>
        <p class="text-muted mb-0">Tinjau seluruh riwayat peminjaman dan kelola keterlambatan pengembalian.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.reports.print', request()->query()) }}" target="_blank" class="btn btn-outline-secondary">
            <i class="bi bi-printer me-1"></i> Print Report
        </a>
        <a href="{{ route('admin.reports.pdf', request()->query()) }}" class="btn btn-danger">
            <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
        </a>
    </div>
</div>

<!-- Search & Filters -->
<form method="GET" action="{{ route('admin.reports') }}" class="mb-4 bg-secondary p-3 rounded border border-secondary" style="background-color: var(--bg-secondary) !important; border-color: var(--border-color) !important;">
    <div class="row g-3">
        <!-- Search General -->
        <div class="col-md-3">
            <label class="form-label text-muted small mb-1">Search</label>
            <div class="input-group">
                <span class="input-group-text bg-dark border-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control bg-dark text-white border-0" placeholder="Title or member name..." value="{{ request('search') }}">
            </div>
        </div>

        <!-- Filter Preset -->
        <div class="col-md-3">
            <label class="form-label text-muted small mb-1">Date Preset</label>
            <select name="filter_type" id="filter_type" class="form-select bg-dark text-white border-0">
                <option value="all" {{ request('filter_type', 'all') === 'all' ? 'selected' : '' }}>All Time</option>
                <option value="today" {{ request('filter_type') === 'today' ? 'selected' : '' }}>Today</option>
                <option value="week" {{ request('filter_type') === 'week' ? 'selected' : '' }}>This Week</option>
                <option value="month" {{ request('filter_type') === 'month' ? 'selected' : '' }}>This Month</option>
                <option value="year" {{ request('filter_type') === 'year' ? 'selected' : '' }}>This Year</option>
                <option value="custom" {{ request('filter_type') === 'custom' ? 'selected' : '' }}>Custom Date Range</option>
            </select>
        </div>

        <!-- Custom Start Date -->
        <div class="col-md-3">
            <label class="form-label text-muted small mb-1">Start Date</label>
            <input type="date" name="start_date" id="start_date" class="form-control bg-dark text-white border-0" value="{{ request('start_date') }}">
        </div>

        <!-- Custom End Date -->
        <div class="col-md-3">
            <label class="form-label text-muted small mb-1">End Date</label>
            <input type="date" name="end_date" id="end_date" class="form-control bg-dark text-white border-0" value="{{ request('end_date') }}">
        </div>
    </div>
    
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div>
            <!-- Refresh Button -->
            <button type="button" onclick="window.location.reload();" class="btn btn-outline-secondary btn-sm" title="Refresh Page">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.reports') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-x-lg me-1"></i> Reset
            </a>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-funnel me-1"></i> Apply Filter
            </button>
        </div>
    </div>
</form>

<!-- Statistics Cards -->
<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-3 mb-4">
    <div class="col">
        <div class="card h-100" style="background-color: var(--bg-secondary) !important; border: 1px solid var(--border-color) !important;">
            <div class="card-body">
                <p class="text-muted small mb-1">Total Borrowed</p>
                <h4 class="fw-bold text-white mb-0">{{ $totalBorrowed }}</h4>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card h-100" style="background-color: var(--bg-secondary) !important; border: 1px solid var(--border-color) !important;">
            <div class="card-body">
                <p class="text-muted small mb-1">Total Returned</p>
                <h4 class="fw-bold text-success mb-0">{{ $totalReturned }}</h4>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card h-100" style="background-color: var(--bg-secondary) !important; border: 1px solid var(--border-color) !important;">
            <div class="card-body">
                <p class="text-muted small mb-1">Currently Borrowed</p>
                <h4 class="fw-bold text-warning mb-0">{{ $totalCurrentlyBorrowed }}</h4>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card h-100" style="background-color: var(--bg-secondary) !important; border: 1px solid var(--border-color) !important;">
            <div class="card-body">
                <p class="text-muted small mb-1">Total Overdue</p>
                <h4 class="fw-bold text-danger mb-0">{{ $totalOverdue }}</h4>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card h-100" style="background-color: var(--bg-secondary) !important; border: 1px solid var(--border-color) !important;">
            <div class="card-body">
                <p class="text-muted small mb-1">Active Members</p>
                <h4 class="fw-bold text-info mb-0">{{ $totalMembersBorrowing }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4 d-print-none" style="background-color: var(--bg-secondary) !important; border: 1px solid var(--border-color) !important;">
    <div class="card-header bg-transparent border-bottom" style="border-color: var(--border-color) !important;">
        <ul class="nav nav-tabs card-header-tabs" id="reportTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="all-history-tab" data-bs-toggle="tab" data-bs-target="#all-history" type="button" role="tab" aria-controls="all-history" aria-selected="true">Semua Riwayat</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link position-relative" id="overdue-tab" data-bs-toggle="tab" data-bs-target="#overdue" type="button" role="tab" aria-controls="overdue" aria-selected="false">
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
                                <th>Batas Kembali (Due)</th>
                                <th>Tgl Pengembalian</th>
                                <th>Durasi</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($history as $h)
                                @php
                                    $hMember = \App\Models\Member::where('email', $h->user->email)->first();
                                @endphp
                                <tr>
                                    <td>#{{ $h->id }}</td>
                                    <td>
                                        <div><strong>{{ $h->user->name ?? 'User' }}</strong></div>
                                        <small class="text-muted">{{ $hMember->kode_anggota ?? '-' }}</small>
                                    </td>
                                    <td class="text-white">
                                        {{ $h->book->judul ?? 'Buku' }}
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($h->borrow_date)->format('d M Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($h->due_date)->format('d M Y') }}</td>
                                    <td>
                                        @if($h->return_date)
                                            {{ \Carbon\Carbon::parse($h->return_date)->format('d M Y') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $h->duration_days }} Hari</td>
                                    <td>
                                        @php
                                            $dispStatus = $h->displayStatus();
                                            $badgeClass = 'bg-warning text-dark';
                                            if ($dispStatus === 'Dikembalikan') {
                                                $badgeClass = 'bg-success';
                                            } elseif (str_contains($dispStatus, 'Terlambat')) {
                                                $badgeClass = 'bg-danger';
                                            }
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">
                                            {{ $dispStatus }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $history->links() }}
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
                                @php
                                    $obMember = \App\Models\Member::where('email', $ob->user->email)->first();
                                @endphp
                                <tr>
                                    <td>#{{ $ob->id }}</td>
                                    <td>
                                        <div><strong>{{ $ob->user->name ?? 'User' }}</strong></div>
                                        <small class="text-muted">{{ $obMember->telepon ?? '-' }}</small>
                                    </td>
                                    <td class="text-white">
                                        {{ $ob->book->judul ?? 'Buku' }}
                                    </td>
                                    <td class="text-danger">{{ \Carbon\Carbon::parse($ob->due_date)->format('d M Y') }}</td>
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

<style>
    /* Custom Tab Styling for Reports */
    .nav-tabs {
        border-bottom: 1px solid var(--border-color) !important;
    }

    .nav-tabs .nav-link {
        color: var(--text-secondary) !important;
        background: transparent !important;
        border: 1px solid transparent !important;
        border-bottom: none !important;
        padding: 10px 20px !important;
        font-weight: 500 !important;
        transition: all 0.2s ease-in-out !important;
        border-top-left-radius: 6px !important;
        border-top-right-radius: 6px !important;
    }

    .nav-tabs .nav-link:hover {
        color: var(--text-primary) !important;
        background: rgba(59, 130, 246, 0.05) !important;
        border-color: var(--border-color) var(--border-color) transparent !important;
    }

    .nav-tabs .nav-link.active {
        color: var(--text-primary) !important;
        background: var(--bg-dark) !important;
        border-color: var(--border-color) var(--border-color) var(--bg-secondary) !important;
        font-weight: 600 !important;
        box-shadow: inset 0 3px 0 var(--primary-blue) !important;
    }
</style>

<script>
$(document).ready(function() {
    $('#filter_type').on('change', function() {
        $(this).closest('form').submit();
    });
    
    function toggleDateInputs() {
        if ($('#filter_type').val() === 'custom') {
            $('#start_date').prop('disabled', false);
            $('#end_date').prop('disabled', false);
        } else {
            $('#start_date').prop('disabled', true);
            $('#end_date').prop('disabled', true);
        }
    }
    
    toggleDateInputs();
    $('#filter_type').on('change', toggleDateInputs);
});
</script>

@endsection
