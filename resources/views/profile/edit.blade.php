@extends('layouts.app')

@section('content')

<h2 class="mb-4">Pengaturan Profil</h2>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-transparent py-3 text-white fw-bold" style="border-color: var(--border-color) !important;">
                <i class="bi bi-person-gear text-primary"></i> Ubah Data Diri & Keamanan
            </div>
            <div class="card-body">
                <form action="{{ route('member.profile.update') }}" method="POST">
                    @csrf

                    <!-- Name Field -->
                    <div class="mb-3">
                        <label class="form-label" for="name">Nama Lengkap</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                            value="{{ old('name', $member->nama) }}" required>
                        @error('name')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Read-Only Email Field (Security restriction) -->
                    <div class="mb-3">
                        <label class="form-label" for="email">Alamat Email</label>
                        <input type="email" id="email" class="form-control" value="{{ $member->email }}" readonly disabled>
                        <div class="form-text text-muted small mt-1">Alamat email tidak dapat diubah demi alasan keamanan akun.</div>
                    </div>

                    <hr class="my-4" style="border-color: var(--border-color) !important;">

                    <!-- Password Field -->
                    <div class="mb-3">
                        <label class="form-label" for="password">Password Baru</label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror">
                        <div class="form-text text-muted small mt-1">Biarkan kosong jika tidak ingin mengubah password saat ini.</div>
                        @error('password')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Password Confirmation -->
                    <div class="mb-4">
                        <label class="form-label" for="password_confirmation">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-primary py-2 px-4">
                        <i class="bi bi-save"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
