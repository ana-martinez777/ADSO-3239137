<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    use HasVersion4Uuids;

    // Equipment attributes that can be mass-assigned.
    protected $fillable = [
        'reference',
        'category_id',
        'total_units',
        'available_units',
        'deleted_at',
    ];

    // The table does not use created_at or updated_at.
    public $timestamps = false;

    // Casts deleted_at to a date so it can be used in PHP.
    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    // Each piece of equipment belongs to one category.
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Loan items for this equipment.
    public function loanItems(): HasMany
    {
        return $this->hasMany(LoanItem::class);
    }

    // Reference history of this equipment.
    public function referenceHistory(): HasMany
    {
        return $this->hasMany(EquipmentReferenceHistory::class);
    }
}