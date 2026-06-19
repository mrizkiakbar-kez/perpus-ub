@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>Kategori Buku</h2>
        <p class="text-muted mb-0">Kelola kategori koleksi perpustakaan Anda.</p>
    </div>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Tambah Kategori
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        @if($categories->count())
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0" style="--bs-table-bg: transparent; --bs-table-border-color: var(--border-color); vertical-align: middle;">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width: 80px;">#</th>
                            <th>Nama Kategori</th>
                            <th>Slug</th>
                            <th>Jumlah Buku</th>
                            <th class="text-end pe-4" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $index => $category)
                            <tr>
                                <td class="ps-4 text-muted">{{ $index + 1 }}</td>
                                <td class="fw-bold text-white">{{ $category->name }}</td>
                                <td class="text-muted"><code>{{ $category->slug }}</code></td>
                                <td>
                                    <span class="badge bg-primary">{{ $category->books_count }} Buku</span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-tags"></i>
                <h5>Belum ada kategori</h5>
                <p class="text-muted mb-3">Mulai dengan menambahkan kategori buku baru.</p>
                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">Tambah Kategori Baru</a>
            </div>
        @endif
    </div>
</div>

@endsection
