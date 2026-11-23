<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanItem extends Model
{
    use HasVersion4Uuids;

    // Define los campos que pueden asignarse masivamente.
    protected $fillable = [
        'loan_id',
        'equipment_id',
        'equipment_reference',
        'category_name',
        'quantity',
        'due_at',
        'returned_at',
    ];

    // La tabla no utiliza created_at ni updated_at.
    public $timestamps = false;

    // Define los tipos de datos de las fechas.
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'due_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    // Relaciona el detalle con su préstamo.
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    // Relaciona el detalle con el implemento prestado.
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }
}