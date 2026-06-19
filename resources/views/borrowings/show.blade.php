@extends('layouts.app')

@section('content')

<div class="mb-4 d-flex justify-content-between align-items-center">
    <h2>Detail Peminjaman #{{ $borrowing->id }}</h2>
    <a href="{{ Auth::check() && Auth::user()->role === 'admin' ? route('admin.borrowings.index') : route('borrowings.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <div class="row mb-3 pb-3" style="border-bottom: 1px solid var(--border-color);">
                    <div class="col-md-6">
                        <p class="text-muted small">Member</p>
                        <p class="h6 text-white">{{ $borrowing->member->nama }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small">Status</p>
                        <p>
                            @php
                                $dispStatus = $borrowing->displayStatus();
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
                        </p>
                    </div>
                </div>

                <div class="row mb-3 pb-3" style="border-bottom: 1px solid var(--border-color);">
                    <div class="col-md-6">
                        <p class="text-muted small">Tanggal Pinjam</p>
                        <p class="text-white">{{ \Carbon\Carbon::parse($borrowing->borrow_date)->format('d M Y') }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small">Batas Kembali</p>
                        <p class="text-white">{{ \Carbon\Carbon::parse($borrowing->return_date)->format('d M Y') }}</p>
                    </div>
                </div>

                @if($borrowing->returned_at)
                <div class="alert alert-success mb-3">
                    <i class="bi bi-check-circle"></i> Sudah dikembalikan pada {{ \Carbon\Carbon::parse($borrowing->returned_at)->format('d M Y H:i') }}
                </div>
                @endif

                @if($borrowing->isOverdue())
                    @if($borrowing->status === 'Dipinjam')
                        <div class="alert alert-danger mb-3">
                            <i class="bi bi-exclamation-triangle"></i> <strong>Telat {{ $borrowing->daysLate() }} hari (Berjalan)</strong>
                            <br>Denda sementara: <strong>Rp {{ number_format($borrowing->calculatePenalty()) }}</strong>
                        </div>
                    @else
                        <div class="alert alert-danger mb-3">
                            <i class="bi bi-exclamation-triangle"></i> <strong>Dikembalikan telat {{ $borrowing->daysLate() }} hari</strong>
                            <br>Denda dikenakan: <strong>Rp {{ number_format($borrowing->calculatePenalty()) }}</strong>
                        </div>
                    @endif
                @endif

                <h5 class="mb-3 text-white">Item Peminjaman</h5>
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0" style="--bs-table-bg: transparent; --bs-table-border-color: var(--border-color);">
                        <thead style="border-bottom: 1px solid var(--border-color);">
                            <tr>
                                <th style="color: var(--text-muted);">Buku</th>
                                <th style="color: var(--text-muted); width: 100px;">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($borrowing->borrowingDetails as $d)
                                <tr style="border-color: var(--border-color);">
                                    <td class="text-white">{{ $d->book->judul }}</td>
                                    <td class="text-white">{{ $d->qty }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($borrowing->status === 'Dipinjam')
        <div class="mt-3">
            <form action="{{ Auth::check() && Auth::user()->role === 'admin' ? route('admin.borrowings.return', $borrowing->id) : route('borrowings.return', $borrowing->id) }}" method="POST" onsubmit="return confirm('Yakin ingin memproses pengembalian?')">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-arrow-counterclockwise"></i> Pengembalian Buku
                </button>
            </form>
        </div>
        @endif
    </div>
</div>

@endsection
