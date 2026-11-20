<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Athlete extends Model
{
    use HasVersion4Uuids;

    // Define los atributos que pueden asignarse masivamente.
    protected $fillable = [
        'full_name',
        'document_number',
    ];

    // Indica que la tabla no utiliza created_at ni updated_at.
    public $timestamps = false;
    
    // Permite consultar todos los préstamos de un deportista.
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }
}