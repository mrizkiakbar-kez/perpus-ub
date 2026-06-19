@extends('layouts.app')

@section('content')

<h2 class="mb-4">Buat Peminjaman</h2>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.borrowings.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Member</label>
                        <select name="member_id" class="form-select @error('member_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Member --</option>
                            @foreach($members as $m)
                                <option value="{{ $m->id }}">{{ $m->nama }} ({{ $m->kode_anggota }})</option>
                            @endforeach
                        </select>
                        @error('member_id')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Kembali (Due Date)</label>
                        <input type="date" name="return_date" class="form-control @error('return_date') is-invalid @enderror" required value="{{ old('return_date', now()->addDays(7)->toDateString()) }}">
                        @error('return_date')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>

                    <h5 class="mb-3">Item Peminjaman</h5>

                    <div id="items" class="mb-3">
                        <div class="item-row mb-2" style="display: flex; gap: 10px;">
                            <select name="items[0][book_id]" class="form-select @error('items.0.book_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Buku --</option>
                                @foreach($books as $book)
                                    <option value="{{ $book->id }}">{{ $book->judul }} (stok: {{ $book->stok }})</option>
                                @endforeach
                            </select>
                            <input type="number" name="items[0][qty]" class="form-control @error('items.0.qty') is-invalid @enderror" min="1" value="1" style="width: 100px;" required>
                            <button type="button" class="btn btn-danger btn-sm remove-item">-</button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <button type="button" id="add-item" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-plus-lg"></i> Tambah Item
                        </button>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                        <a href="{{ route('admin.borrowings.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let idx = 1;
    $('#add-item').on('click', function(){
        const row = `
            <div class="item-row mb-2" style="display: flex; gap: 10px;">
                <select name="items[${idx}][book_id]" class="form-select" required>
                    <option value="">-- Pilih Buku --</option>
                    @foreach($books as $book)
                        <option value="{{ $book->id }}">{{ $book->judul }} (stok: {{ $book->stok }})</option>
                    @endforeach
                </select>
                <input type="number" name="items[${idx}][qty]" class="form-control" min="1" value="1" style="width: 100px;" required>
                <button type="button" class="btn btn-danger btn-sm remove-item">-</button>
            </div>`;
        $('#items').append(row);
        idx++;
    });

    $(document).on('click', '.remove-item', function(){
        $(this).closest('.item-row').remove();
    });
</script>

@endsection
