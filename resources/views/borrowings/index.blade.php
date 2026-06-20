@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>{{ Auth::check() && Auth::user()->role === 'admin' ? 'Kelola Peminjaman' : 'Peminjaman Saya' }}</h2>
    @if(Auth::check() && Auth::user()->role === 'admin')
        <a href="{{ route('admin.borrowings.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Buat Peminjaman
        </a>
    @endif
</div>

<!-- Filters -->
<div class="d-flex gap-2 mb-4">
    <a href="{{ request()->fullUrlWithQuery(['filter' => 'all']) }}" class="btn btn-sm {{ $filter === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">Semua</a>
    <a href="{{ request()->fullUrlWithQuery(['filter' => 'borrowed']) }}" class="btn btn-sm {{ $filter === 'borrowed' ? 'btn-primary' : 'btn-outline-secondary' }}">Dipinjam</a>
    <a href="{{ request()->fullUrlWithQuery(['filter' => 'returned']) }}" class="btn btn-sm {{ $filter === 'returned' ? 'btn-primary' : 'btn-outline-secondary' }}">Dikembalikan</a>
    <a href="{{ request()->fullUrlWithQuery(['filter' => 'late']) }}" class="btn btn-sm {{ $filter === 'late' ? 'btn-primary' : 'btn-outline-secondary' }}">Terlambat</a>
</div>

@if($borrowings->count())
    <div class="card">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0" style="--bs-table-bg: transparent; --bs-table-border-color: var(--border-color); vertical-align: middle;">
                <thead style="border-bottom: 1px solid var(--border-color);">
                    <tr>
                        @if(Auth::check() && Auth::user()->role === 'admin')
                            <th class="ps-4" style="color: var(--text-muted);">Member</th>
                        @endif
                        <th class="{{ Auth::check() && Auth::user()->role === 'admin' ? '' : 'ps-4' }}" style="color: var(--text-muted);">Buku</th>
                        <th style="color: var(--text-muted);">Tanggal Pinjam</th>
                        <th style="color: var(--text-muted);">Batas Kembali (Due)</th>
                        <th style="color: var(--text-muted);">Tanggal Kembali</th>
                        <th style="color: var(--text-muted);">Denda</th>
                        <th style="color: var(--text-muted);">Status</th>
                        <th class="text-end pe-4" style="color: var(--text-muted); width: 220px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($borrowings as $b)
                    <tr style="border-color: var(--border-color);">
                        @if(Auth::check() && Auth::user()->role === 'admin')
                            <td class="ps-4 fw-bold text-white">{{ $b->user->name ?? 'User' }}</td>
                        @endif
                        <td class="{{ Auth::check() && Auth::user()->role === 'admin' ? '' : 'ps-4' }} text-white">
                            {{ $b->book->judul ?? 'Buku' }}
                        </td>
                        <td class="text-muted">{{ \Carbon\Carbon::parse($b->borrow_date)->format('d M Y') }}</td>
                        <td>
                            <span class="{{ $b->status === 'borrowed' && \Carbon\Carbon::parse($b->due_date)->isPast() ? 'text-danger fw-bold' : 'text-muted' }}">
                                {{ \Carbon\Carbon::parse($b->due_date)->format('d M Y') }}
                            </span>
                        </td>
                        <td class="text-muted">
                            {{ $b->return_date ? \Carbon\Carbon::parse($b->return_date)->format('d M Y') : '-' }}
                        </td>
                        <td class="text-muted">
                            @if($b->late_penalty > 0)
                                <span class="text-danger fw-bold">Rp {{ number_format($b->late_penalty) }}</span>
                            @elseif($b->status === 'borrowed' && $b->isOverdue())
                                <span class="text-warning">Rp {{ number_format($b->calculatePenalty()) }} (Est)</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
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
                        <td class="text-end pe-4">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ Auth::check() && Auth::user()->role === 'admin' ? route('admin.borrowings.show', $b->id) : route('borrowings.show', $b->id) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                                @if($b->status === 'borrowed')
                                    <form action="{{ Auth::check() && Auth::user()->role === 'admin' ? route('admin.borrowings.return', $b->id) : route('borrowings.return', $b->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin memproses pengembalian?')">
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
        <div class="empty-state">
            <i class="bi bi-journal-check"></i>
            <h5>Belum ada peminjaman</h5>
            <p class="text-muted mb-3">Tidak ditemukan data peminjaman dengan filter "{{ $filter }}".</p>
            @if(Auth::check() && Auth::user()->role === 'admin')
                <a href="{{ route('admin.borrowings.create') }}" class="btn btn-primary btn-sm">Buat Peminjaman Baru</a>
            @endif
        </div>
    </div>
@endif

@endsection
