<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected $fillable = [
        'kode_buku',
        'judul',
        'category_id',
        'penulis',
        'penerbit',
        'tahun_terbit',
        'stok',
        'cover_image',
        'deskripsi'
    ];

    public function borrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function isAvailable(int $qty = 1): bool
    {
        return $this->stok >= $qty;
    }
}