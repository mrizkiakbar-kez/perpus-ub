@extends('layouts.app')

@section('content')

<h2 class="mb-4">Dashboard Admin</h2>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-2">Total Buku</p>
                        <h3>{{ $totalBooks ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-book" style="font-size: 32px; color: var(--primary-blue); opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-2">Total Anggota</p>
                        <h3>{{ $totalMembers ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-people" style="font-size: 32px; color: var(--primary-blue); opacity: 0.3;"></i>
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
                    <i class="bi bi-journal-check" style="font-size: 32px; color: var(--primary-blue); opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="mb-3">Aktivitas Peminjaman Terbaru</h5>
        @if($recent->count())
            <div class="list-group list-group-flush">
                @foreach($recent as $r)
                    <div class="list-group-item d-flex justify-content-between align-items-start" style="background: transparent; border-color: var(--border-color);">
                        <div>
                            <h6 class="mb-0 text-white">{{ $r->user->name ?? 'User' }} <span class="text-muted fw-normal">meminjam</span> {{ $r->book->judul ?? 'Buku' }}</h6>
                            <small class="text-muted">{{ $r->created_at->format('d M Y - H:i') }}</small>
                        </div>
                        @php
                            $dispStatus = $r->displayStatus();
                            $badgeClass = 'bg-warning text-dark';
                            if ($dispStatus === 'Dikembalikan') {
                                $badgeClass = 'bg-success';
                            } elseif (str_contains($dispStatus, 'Terlambat')) {
                                $badgeClass = 'bg-danger';
                            }
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $dispStatus }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-muted mb-0">Belum ada aktivitas.</p>
        @endif
    </div>
</div>

@endsection
