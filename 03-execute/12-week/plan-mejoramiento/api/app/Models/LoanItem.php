<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanItem extends Model
{
    use HasVersion4Uuids;

    // Fields that can be mass-assigned.
    protected $fillable = [
        'loan_id',
        'equipment_id',
        'equipment_reference',
        'category_name',
        'quantity',
        'due_at',
        'returned_at',
    ];

    // The table does not use created_at or updated_at.
    public $timestamps = false;

    // Data types of the date attributes.
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'due_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    // The loan this item belongs to.
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    // The equipment that was loaned.
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }
}