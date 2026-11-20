<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasVersion4Uuids;

    // Permite asignar el nombre de la categoría.
    protected $fillable = [
        'name',
    ];

    // Indica que la tabla no utiliza created_at ni updated_at.
    public $timestamps = false;

    // Permite consultar los implementos pertenecientes a una categoría.
    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }
}