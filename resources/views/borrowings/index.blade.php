@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>{{ Auth::check() && Auth::user()->role === 'admin' ? 'Kelola Peminjaman' : 'Peminjaman Saya' }}</h2>
    @if(Auth::check() && Auth::user()->role === 'admin')
        <a href="{{ route('admin.borrowings.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Buat Peminjaman
        </a>
    @endif
</div>

@if($borrowings->count())
    <div class="card">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0" style="--bs-table-bg: transparent; --bs-table-border-color: var(--border-color); vertical-align: middle;">
                <thead style="border-bottom: 1px solid var(--border-color);">
                    <tr>
                        @if(Auth::check() && Auth::user()->role === 'admin')
                            <th class="ps-4" style="color: var(--text-muted);">Member</th>
                        @else
                            <th class="ps-4" style="color: var(--text-muted);">Buku</th>
                        @endif
                        <th style="color: var(--text-muted);">Tanggal Pinjam</th>
                        <th style="color: var(--text-muted);">Batas Kembali</th>
                        <th style="color: var(--text-muted);">Status</th>
                        <th class="text-end pe-4" style="color: var(--text-muted); width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($borrowings as $b)
                    <tr style="border-color: var(--border-color);">
                        @if(Auth::check() && Auth::user()->role === 'admin')
                            <td class="ps-4 fw-bold text-white">{{ $b->member->nama }}</td>
                        @else
                            <td class="ps-4 text-white">
                                @foreach($b->borrowingDetails as $detail)
                                    <div>{{ $detail->book->judul }} <span class="text-muted small">({{ $detail->qty }} pcs)</span></div>
                                @endforeach
                            </td>
                        @endif
                        <td class="text-muted">{{ \Carbon\Carbon::parse($b->borrow_date)->format('d M Y') }}</td>
                        <td>
                            <span class="{{ $b->status === 'Dipinjam' && \Carbon\Carbon::parse($b->return_date)->isPast() ? 'text-danger fw-bold' : 'text-muted' }}">
                                {{ \Carbon\Carbon::parse($b->return_date)->format('d M Y') }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $b->status === 'Dipinjam' ? 'bg-warning text-dark' : 'bg-success' }}">
                                {{ $b->status }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ Auth::check() ? route('admin.borrowings.show', $b->id) : route('borrowings.show', $b->id) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i> Detail
                            </a>
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
            <p class="text-muted mb-3">Tidak ditemukan data peminjaman.</p>
            @if(Auth::check() && Auth::user()->role === 'admin')
                <a href="{{ route('admin.borrowings.create') }}" class="btn btn-primary btn-sm">Buat Peminjaman Baru</a>
            @endif
        </div>
    </div>
@endif

@endsection
