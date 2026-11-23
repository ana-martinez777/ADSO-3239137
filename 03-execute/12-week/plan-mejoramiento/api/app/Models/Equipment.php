<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    use HasVersion4Uuids;

    // Permite asignar los atributos del implemento.
    protected $fillable = [
        'reference',
        'category_id',
        'total_units',
        'available_units',
        'deleted_at',
    ];

    // Indica que la tabla no utiliza created_at ni updated_at.
    public $timestamps = false;

    // Convierte deleted_at en una fecha para trabajar con ella en PHP.
    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    // Define que cada implemento pertenece a una categoría.
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Permite consultar los detalles de préstamos de este implemento.
    public function loanItems(): HasMany
    {
        return $this->hasMany(LoanItem::class);
    }

    // Permite consultar el historial de referencias del implemento.
    public function referenceHistory(): HasMany
    {
        return $this->hasMany(EquipmentReferenceHistory::class);
    }
}