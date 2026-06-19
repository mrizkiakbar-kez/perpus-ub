@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Anggota</h2>
    <a href="{{ route('admin.members.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Tambah Anggota
    </a>
</div>

@if($members->count())
    <div class="row g-4">
        @foreach($members as $m)
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="text-muted small mb-2">{{ $m->kode_anggota }}</div>
                        <h5 class="card-title mb-2 text-white">{{ $m->nama }}</h5>
                        <p class="card-text text-muted small mb-1">{{ $m->email }}</p>
                        <p class="card-text text-muted small mb-3">{{ $m->telepon }}</p>
                        
                        <div class="mt-auto pt-3" style="border-top: 1px solid var(--border-color);">
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.members.edit', $m->id) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.members.destroy', $m->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus anggota ini?')" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
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
            <p class="text-muted mb-3">Mulai dengan menambahkan anggota perpustakaan baru.</p>
            <a href="{{ route('admin.members.create') }}" class="btn btn-primary btn-sm">Tambah Anggota Baru</a>
        </div>
    </div>
@endif

@endsection
