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
        return now()->startOfDay()->gt(\Carbon\Carbon::parse($this->return_date));
    }

    public function daysLate(): int
    {
        $due = \Carbon\Carbon::parse($this->return_date)->startOfDay();
        $now = now()->startOfDay();
        if ($now->lte($due)) return 0;
        return $now->diffInDays($due);
    }

    public function calculatePenalty(int $perDay = 1000): int
    {
        return $this->daysLate() * $perDay;
    }
}