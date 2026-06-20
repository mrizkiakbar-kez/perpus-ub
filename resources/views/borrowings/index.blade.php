@extends('layouts.app')

@section('content')

@if(Auth::check() && Auth::user()->role === 'admin')
    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4" style="background: rgba(59, 130, 246, 0.15); color: var(--primary-blue);">
        <i class="bi bi-info-circle-fill me-3 fs-4"></i>
        <div>
            <h5 class="alert-heading mb-1 fw-bold">Monitoring Dashboard (Read-Only)</h5>
            <p class="mb-0 small">This page operates as a read-only transaction monitor. Administrators cannot create, return, modify, or delete borrowing records.</p>
        </div>
    </div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>{{ Auth::check() && Auth::user()->role === 'admin' ? 'Monitoring Peminjaman' : 'Peminjaman Saya' }}</h2>
</div>

<!-- Filters & Search for Admin -->
@if(Auth::check() && Auth::user()->role === 'admin')
    <form method="GET" action="{{ route('admin.borrowings.index') }}" class="mb-4 bg-secondary p-3 rounded border border-secondary" style="background-color: var(--bg-secondary) !important; border-color: var(--border-color) !important;">
        <div class="row g-3">
            <!-- Search General -->
            <div class="col-md-3">
                <label class="form-label text-muted small mb-1">Search</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control bg-dark text-white border-0" placeholder="Title or member name..." value="{{ request('search') }}">
                </div>
            </div>

            <!-- Filter Member Name -->
            <div class="col-md-2">
                <label class="form-label text-muted small mb-1">Member Name</label>
                <input type="text" name="member_name" class="form-control bg-dark text-white border-0" placeholder="Member name..." value="{{ request('member_name') }}">
            </div>

            <!-- Filter Book Title -->
            <div class="col-md-2">
                <label class="form-label text-muted small mb-1">Book Title</label>
                <input type="text" name="book_title" class="form-control bg-dark text-white border-0" placeholder="Book title..." value="{{ request('book_title') }}">
            </div>

            <!-- Status Filter -->
            <div class="col-md-2">
                <label class="form-label text-muted small mb-1">Status</label>
                <select name="status" class="form-select bg-dark text-white border-0">
                    <option value="">All Statuses</option>
                    <option value="borrowed" {{ request('status') === 'borrowed' || request('filter') === 'borrowed' ? 'selected' : '' }}>Borrowed (Active)</option>
                    <option value="returned" {{ request('status') === 'returned' || request('filter') === 'returned' ? 'selected' : '' }}>Returned</option>
                    <option value="late" {{ request('status') === 'late' || request('filter') === 'late' ? 'selected' : '' }}>Late</option>
                </select>
            </div>

            <!-- Sort By -->
            <div class="col-md-3">
                <label class="form-label text-muted small mb-1">Sort By</label>
                <div class="d-flex gap-2">
                    <select name="sort_by" class="form-select bg-dark text-white border-0">
                        <option value="borrow_date" {{ request('sort_by') === 'borrow_date' ? 'selected' : '' }}>Borrow Date</option>
                        <option value="due_date" {{ request('sort_by') === 'due_date' ? 'selected' : '' }}>Due Date</option>
                    </select>
                    <select name="sort_order" class="form-select bg-dark text-white border-0" style="width: 100px;">
                        <option value="desc" {{ request('sort_order', 'desc') === 'desc' ? 'selected' : '' }}>DESC</option>
                        <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>ASC</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-end gap-2 mt-3">
            <a href="{{ route('admin.borrowings.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-x-lg"></i> Reset
            </a>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-funnel"></i> Filter & Sort
            </button>
        </div>
    </form>
@else
    <!-- Legacy/Quick Filters for Members -->
    <div class="d-flex gap-2 mb-4">
        <a href="{{ request()->fullUrlWithQuery(['filter' => 'all']) }}" class="btn btn-sm {{ $filter === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">Semua</a>
        <a href="{{ request()->fullUrlWithQuery(['filter' => 'borrowed']) }}" class="btn btn-sm {{ $filter === 'borrowed' ? 'btn-primary' : 'btn-outline-secondary' }}">Dipinjam</a>
        <a href="{{ request()->fullUrlWithQuery(['filter' => 'returned']) }}" class="btn btn-sm {{ $filter === 'returned' ? 'btn-primary' : 'btn-outline-secondary' }}">Dikembalikan</a>
        <a href="{{ request()->fullUrlWithQuery(['filter' => 'late']) }}" class="btn btn-sm {{ $filter === 'late' ? 'btn-primary' : 'btn-outline-secondary' }}">Terlambat</a>
    </div>
@endif

@if($borrowings->count())
    <div class="card">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0" style="--bs-table-bg: transparent; --bs-table-border-color: var(--border-color); vertical-align: middle;">
                <thead style="border-bottom: 1px solid var(--border-color);">
                    <tr>
                        <th class="ps-4" style="color: var(--text-muted); width: 80px;">Cover</th>
                        <th style="color: var(--text-muted);">Buku</th>
                        @if(Auth::check() && Auth::user()->role === 'admin')
                            <th style="color: var(--text-muted);">Member</th>
                        @endif
                        <th style="color: var(--text-muted);">Tanggal Pinjam</th>
                        <th style="color: var(--text-muted);">Batas Kembali (Due)</th>
                        <th style="color: var(--text-muted);">Tanggal Kembali</th>
                        @if(Auth::check() && Auth::user()->role === 'admin')
                            <th style="color: var(--text-muted);">Sisa Waktu</th>
                        @endif
                        <th style="color: var(--text-muted);">Denda</th>
                        <th style="color: var(--text-muted);">Status</th>
                        <th class="text-end pe-4" style="color: var(--text-muted); width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($borrowings as $b)
                    <tr style="border-color: var(--border-color);">
                        <!-- Book Cover column -->
                        <td class="ps-4 align-middle">
                            @php
                                $gradNum = ($b->book->id ?? 0) % 5 + 1;
                            @endphp
                            <div class="cover-grad-{{ $gradNum }}" style="width: 45px; height: 60px; border-radius: 4px; position: relative; overflow: hidden; border: 1px solid var(--border-color); flex-shrink: 0;">
                                <div style="position: absolute; inset: 0; background-image: radial-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 0); background-size: 4px 4px;"></div>
                                <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);"></div>
                            </div>
                        </td>

                        <!-- Book Title column -->
                        <td class="text-white align-middle fw-bold">
                            {{ $b->book->judul ?? 'Buku' }}
                            <div class="text-muted small" style="font-weight: normal; font-size: 11px;">{{ $b->book->kode_buku ?? '' }}</div>
                        </td>

                        <!-- Member Name (Admin only) -->
                        @if(Auth::check() && Auth::user()->role === 'admin')
                            <td class="text-white align-middle">{{ $b->user->name ?? 'User' }}</td>
                        @endif

                        <!-- Borrow Date -->
                        <td class="text-muted align-middle">{{ \Carbon\Carbon::parse($b->borrow_date)->format('d M Y') }}</td>

                        <!-- Due Date -->
                        <td class="align-middle">
                            <span class="{{ $b->status === 'borrowed' && \Carbon\Carbon::parse($b->due_date)->isPast() ? 'text-danger fw-bold' : 'text-muted' }}">
                                {{ \Carbon\Carbon::parse($b->due_date)->format('d M Y') }}
                            </span>
                        </td>

                        <!-- Return Date -->
                        <td class="text-muted align-middle">
                            {{ $b->return_date ? \Carbon\Carbon::parse($b->return_date)->format('d M Y') : '-' }}
                        </td>

                        <!-- Remaining Days (Admin only) -->
                        @if(Auth::check() && Auth::user()->role === 'admin')
                            <td class="align-middle">
                                @if($b->status === 'borrowed')
                                    @php
                                        $due = \Carbon\Carbon::parse($b->due_date)->startOfDay();
                                        $today = now()->startOfDay();
                                        $isPast = $today->gt($due);
                                        $days = abs((int) $today->diffInDays($due));
                                    @endphp
                                    @if($isPast)
                                        <span class="text-danger fw-bold">Telat {{ $days }} Hari</span>
                                    @else
                                        <span class="text-success">{{ $days }} Hari Lagi</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        @endif

                        <!-- Penalty -->
                        <td class="text-muted align-middle">
                            @if($b->late_penalty > 0)
                                <span class="text-danger fw-bold">Rp {{ number_format($b->late_penalty) }}</span>
                            @elseif($b->status === 'borrowed' && $b->isOverdue())
                                <span class="text-warning">Rp {{ number_format($b->calculatePenalty()) }} (Est)</span>
                            @else
                                -
                            @endif
                        </td>

                        <!-- Status badge -->
                        <td class="align-middle">
                            @php
                                $dispStatus = $b->displayStatus();
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

                        <!-- Actions (Role-dependent) -->
                        <td class="text-end pe-4 align-middle">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ Auth::check() && Auth::user()->role === 'admin' ? route('admin.borrowings.show', $b->id) : route('borrowings.show', $b->id) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                                @if($b->status === 'borrowed' && !(Auth::check() && Auth::user()->role === 'admin'))
                                    <form action="{{ route('borrowings.return', $b->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin memproses pengembalian?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bi bi-arrow-counterclockwise"></i> Kembalikan
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $borrowings->links() }}
    </div>
@else
    <div class="card">
        <div class="empty-state text-center py-5">
            <i class="bi bi-journal-check text-muted" style="font-size: 3rem;"></i>
            <h5 class="mt-3">Belum ada peminjaman</h5>
            <p class="text-muted mb-3">Tidak ditemukan data peminjaman dengan filter saat ini.</p>
        </div>
    </div>
@endif

@endsection
