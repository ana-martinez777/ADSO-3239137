<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Athlete extends Model
{
    use HasVersion4Uuids;

    // Attributes that can be mass-assigned.
    protected $fillable = [
        'full_name',
        'document_number',
    ];

    // The table does not use created_at or updated_at.
    public $timestamps = false;
    
    // All loans of the athlete.
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }
}