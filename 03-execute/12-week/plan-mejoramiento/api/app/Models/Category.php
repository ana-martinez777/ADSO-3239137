<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasVersion4Uuids;

    // Allows mass assignment of the category name.
    protected $fillable = [
        'name',
    ];

    // The table does not use created_at or updated_at.
    public $timestamps = false;

    // Equipment that belongs to the category.
    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }
}