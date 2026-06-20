<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Borrowing extends Model
{
    protected $fillable = [
        'user_id',
        'book_id',
        'borrow_date',
        'due_date',
        'return_date',
        'status',
        'duration_days',
        'late_penalty'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function isOverdue(): bool
    {
        $due = \Carbon\Carbon::parse($this->due_date)->startOfDay();
        $actualReturnDate = $this->return_date 
            ? \Carbon\Carbon::parse($this->return_date)->startOfDay() 
            : now()->startOfDay();
            
        return $actualReturnDate->gt($due);
    }

    public function daysLate(): int
    {
        $due = \Carbon\Carbon::parse($this->due_date)->startOfDay();
        
        $actualReturnDate = $this->return_date 
            ? \Carbon\Carbon::parse($this->return_date)->startOfDay() 
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
        if ($this->status === 'returned') {
            return 'Dikembalikan';
        }
        
        if ($this->status === 'late') {
            return 'Terlambat (Dikembalikan)';
        }
        
        if ($this->status === 'borrowed' && now()->startOfDay()->gt(\Carbon\Carbon::parse($this->due_date)->startOfDay())) {
            return 'Terlambat';
        }
        
        return 'Dipinjam';
    }
}