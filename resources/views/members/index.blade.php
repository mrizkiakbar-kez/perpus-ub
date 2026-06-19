@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Anggota (Daftar Pengguna)</h2>
</div>

@if($members->count())
    <div class="row g-4">
        @foreach($members as $m)
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="text-muted small mb-2">{{ $m->kode_anggota }}</div>
                        <h5 class="card-title mb-2 text-white">{{ $m->nama }}</h5>
                        
                        @php
                            $emailParts = explode('@', $m->email);
                            $namePart = $emailParts[0];
                            $domainPart = $emailParts[1] ?? '';
                            $maskedName = strlen($namePart) <= 2 ? $namePart . '***' : substr($namePart, 0, 2) . '***' . substr($namePart, -1);
                            $maskedEmail = $maskedName . '@' . $domainPart;
                        @endphp
                        
                        <p class="card-text text-muted small mb-1" title="Disamarkan untuk privasi">
                            <i class="bi bi-envelope me-1"></i>{{ $maskedEmail }}
                        </p>
                        <p class="card-text text-muted small mb-1">
                            <i class="bi bi-telephone me-1"></i>{{ $m->telepon }}
                        </p>
                        <p class="card-text text-muted small mb-0">
                            <i class="bi bi-geo-alt me-1"></i>{{ $m->alamat }}
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="card">
        <div class="empty-state">
            <i class="bi bi-people"></i>
            <h5>Belum ada anggota</h5>
            <p class="text-muted mb-0">Tidak ditemukan data anggota.</p>
        </div>
    </div>
@endif

@endsection
