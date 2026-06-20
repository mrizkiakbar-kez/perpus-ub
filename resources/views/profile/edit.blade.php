@extends('layouts.app')

@section('content')

<h2 class="mb-4">Edit Profile</h2>

<div class="row">
    <!-- Read-Only Info Card -->
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm" style="background-color: var(--bg-secondary) !important; border: 1px solid var(--border-color) !important;">
            <div class="card-body text-center py-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-dark rounded-circle mb-3 shadow" style="width: 80px; height: 80px; border: 2px solid var(--primary-blue);">
                    <i class="bi bi-person-fill text-primary" style="font-size: 2.5rem;"></i>
                </div>
                <h4 class="fw-bold mb-1 text-white">{{ $member->nama }}</h4>
                <p class="text-muted small mb-3">{{ $member->kode_anggota }}</p>
                <span class="badge bg-primary px-3 py-1 rounded-pill">{{ ucfirst($user->role ?? $member->role) }}</span>
            </div>
            <div class="card-footer bg-dark border-0 p-4" style="border-top: 1px solid var(--border-color) !important;">
                <div class="mb-3">
                    <label class="text-muted small d-block mb-1">Email Address</label>
                    <span class="text-white fw-medium">{{ $member->email }}</span>
                </div>
                <div class="mb-3">
                    <label class="text-muted small d-block mb-1">Role</label>
                    <span class="text-white fw-medium">{{ ucfirst($user->role ?? $member->role) }}</span>
                </div>
                <div class="mb-3">
                    <label class="text-muted small d-block mb-1">Account ID</label>
                    <span class="text-white fw-medium">{{ $member->kode_anggota }}</span>
                </div>
                <div>
                    <label class="text-muted small d-block mb-1">Member Since</label>
                    <span class="text-white fw-medium">{{ $member->created_at->format('d M Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Form Card -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm" style="background-color: var(--bg-secondary) !important; border: 1px solid var(--border-color) !important;">
            <div class="card-header bg-transparent py-3 text-white fw-bold border-0" style="border-bottom: 1px solid var(--border-color) !important;">
                <i class="bi bi-pencil-square text-primary me-2"></i>Ubah Data Diri & Keamanan
            </div>
            <div class="card-body p-4">
                <form action="{{ route('member.profile.update') }}" method="POST">
                    @csrf

                    <!-- Full Name -->
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1" for="name">Nama Lengkap</label>
                        <input type="text" name="name" id="name" class="form-control bg-dark text-white border-0 @error('name') is-invalid @enderror" 
                            style="border: 1px solid var(--border-color) !important;"
                            value="{{ old('name', $member->nama) }}" required>
                        @error('name')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">
                        <!-- Password Field -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small mb-1" for="password">Password Baru</label>
                            <input type="password" name="password" id="password" class="form-control bg-dark text-white border-0 @error('password') is-invalid @enderror"
                                style="border: 1px solid var(--border-color) !important;">
                            <div class="form-text text-muted small mt-1">Biarkan kosong jika tidak ingin mengubah password saat ini.</div>
                            @error('password')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Password Confirmation -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small mb-1" for="password_confirmation">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control bg-dark text-white border-0"
                                style="border: 1px solid var(--border-color) !important;">
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary px-4 py-2">
                            <i class="bi bi-save me-1"></i>Simpan Perubahan
                        </button>
                        <a href="{{ route('member.dashboard') }}" class="btn btn-outline-secondary px-4 py-2">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
