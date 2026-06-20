@extends('layouts.app')

@section('content')

<div class="mb-4">
    <a href="{{ route('admin.members.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar Anggota
    </a>
</div>

<div class="row">
    <!-- Member Profile Card -->
    <div class="col-lg-4 mb-4">
        <div class="card text-white border-0 shadow-sm" style="background-color: var(--bg-secondary) !important; border: 1px solid var(--border-color) !important;">
            <div class="card-body text-center pt-4 pb-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-dark rounded-circle mb-3 shadow" style="width: 90px; height: 90px; border: 2px solid var(--primary-blue);">
                    <i class="bi bi-person-fill text-primary" style="font-size: 3rem;"></i>
                </div>
                <h4 class="fw-bold mb-1">{{ $member->nama }}</h4>
                <p class="text-muted small mb-3">{{ $member->kode_anggota }}</p>
                <span class="badge bg-success px-3 py-2 rounded-pill">Active</span>
            </div>
            <div class="card-footer bg-dark border-0 p-4" style="border-top: 1px solid var(--border-color) !important;">
                <div class="mb-3">
                    <label class="text-muted small d-block mb-1">Email</label>
                    @php
                        $emailParts = explode('@', $member->email);
                        $namePart = $emailParts[0];
                        $domainPart = $emailParts[1] ?? '';
                        $maskedName = strlen($namePart) <= 2 ? $namePart . '***' : substr($namePart, 0, 2) . '***' . substr($namePart, -1);
                        $maskedEmail = $maskedName . '@' . $domainPart;
                    @endphp
                    <span class="text-white fw-medium" title="Disamarkan untuk privasi">{{ $maskedEmail }}</span>
                </div>
                <div class="mb-3">
                    <label class="text-muted small d-block mb-1">Telepon</label>
                    <span class="text-white fw-medium">{{ $member->telepon }}</span>
                </div>
                <div class="mb-3">
                    <label class="text-muted small d-block mb-1">Alamat</label>
                    <span class="text-white fw-medium">{{ $member->alamat }}</span>
                </div>
                <div class="mb-3">
                    <label class="text-muted small d-block mb-1">Role</label>
                    <span class="badge bg-secondary">{{ ucfirst($user->role ?? $member->role) }}</span>
                </div>
                <div>
                    <label class="text-muted small d-block mb-1">Tanggal Terdaftar</label>
                    <span class="text-white fw-medium">{{ $member->created_at->format('d F Y (H:i)') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Member Borrowing History -->
    <div class="col-lg-8">
        <div class="card text-white border-0 shadow-sm mb-4" style="background-color: var(--bg-secondary) !important; border: 1px solid var(--border-color) !important;">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold"><i class="bi bi-clock-history text-primary me-2"></i> Riwayat Peminjaman Buku</h5>
            </div>
            <div class="card-body p-4">
                @if($borrowings->count())
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="border-0 text-muted small">Buku</th>
                                    <th class="border-0 text-muted small">Tgl Pinjam</th>
                                    <th class="border-0 text-muted small">Tgl Kembali</th>
                                    <th class="border-0 text-muted small">Status</th>
                                    <th class="border-0 text-muted small text-end">Denda</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($borrowings as $b)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-white">{{ $b->book->judul ?? 'Buku Dihapus' }}</div>
                                            <div class="small text-muted">{{ $b->book->penulis ?? '' }}</div>
                                        </td>
                                        <td class="small">{{ \Carbon\Carbon::parse($b->borrow_date)->format('d M Y') }}</td>
                                        <td class="small">
                                            @if($b->return_date)
                                                {{ \Carbon\Carbon::parse($b->return_date)->format('d M Y') }}
                                            @else
                                                <span class="{{ \Carbon\Carbon::parse($b->due_date)->isPast() ? 'text-danger fw-bold' : 'text-muted' }}">
                                                    {{ \Carbon\Carbon::parse($b->due_date)->format('d M Y') }} (Batas)
                                                </span>
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
                                            <span class="badge {{ $badgeClass }}">{{ $dispStatus }}</span>
                                        </td>
                                        <td class="text-end small">
                                            @if($b->late_penalty > 0)
                                                <span class="text-danger fw-bold">Rp {{ number_format($b->late_penalty) }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-5 text-center text-muted">
                        <i class="bi bi-journal-x" style="font-size: 3rem;"></i>
                        <h6 class="mt-3">Belum ada riwayat peminjaman</h6>
                        <p class="mb-0 small">Anggota ini belum pernah meminjam buku.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
