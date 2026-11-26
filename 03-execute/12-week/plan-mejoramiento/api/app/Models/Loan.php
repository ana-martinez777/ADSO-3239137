<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasVersion4Uuids;

    // Fields that can be mass-assigned.
    protected $fillable = [
        'athlete_id',
        'user_id',
        'loaned_at',
    ];

    // The table does not use created_at or updated_at.
    public $timestamps = false;

    // Casts loaned_at to a date instance.
    protected function casts(): array
    {
        return [
            'loaned_at' => 'datetime',
        ];
    }

    // The athlete who receives the loan.
    public function athlete(): BelongsTo
    {
        return $this->belongsTo(Athlete::class);
    }

    // The coach who registers the loan.
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Items included in the loan.
    public function loanItems(): HasMany
    {
        return $this->hasMany(LoanItem::class);
    }
}