<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Borrowing extends Model
{
    protected $fillable = [
        'member_id',
        'borrow_date',
        'return_date',
        'status',
        'user_id'
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function borrowingDetails(): HasMany
    {
        return $this->hasMany(BorrowingDetail::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function isOverdue(): bool
    {
        $due = \Carbon\Carbon::parse($this->return_date)->startOfDay();
        $actualReturnDate = $this->returned_at 
            ? \Carbon\Carbon::parse($this->returned_at)->startOfDay() 
            : now()->startOfDay();
            
        return $actualReturnDate->gt($due);
    }

    public function daysLate(): int
    {
        $due = \Carbon\Carbon::parse($this->return_date)->startOfDay();
        
        // If already returned, use returned_at date, otherwise use current date
        $actualReturnDate = $this->returned_at 
            ? \Carbon\Carbon::parse($this->returned_at)->startOfDay() 
            : now()->startOfDay();
            
        if ($actualReturnDate->lte($due)) return 0;
        return abs((int) $actualReturnDate->diffInDays($due));
    }

    public function calculatePenalty(int $perDay = 1000): int
    {
        return $this->daysLate() * $perDay;
    }

    public function displayStatus(): string
    {
        if ($this->status === 'Dikembalikan') {
            if ($this->returned_at && \Carbon\Carbon::parse($this->returned_at)->startOfDay()->gt(\Carbon\Carbon::parse($this->return_date)->startOfDay())) {
                return 'Terlambat (Dikembalikan)';
            }
            return 'Dikembalikan';
        }
        
        if (now()->startOfDay()->gt(\Carbon\Carbon::parse($this->return_date)->startOfDay())) {
            return 'Terlambat';
        }
        
        return 'Dipinjam';
    }
}