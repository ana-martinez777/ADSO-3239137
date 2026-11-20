<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasVersion4Uuids;

    // Define los campos que pueden asignarse masivamente.
    protected $fillable = [
        'athlete_id',
    ];

    // La tabla no utiliza created_at ni updated_at.
    public $timestamps = false;

    // Convierte loaned_at en una instancia de fecha.
    protected function casts(): array
    {
        return [
            'loaned_at' => 'datetime',
        ];
    }

    // Relaciona el préstamo con el deportista que lo recibe.
    public function athlete(): BelongsTo
    {
        return $this->belongsTo(Athlete::class);
    }

    // Relaciona el préstamo con el entrenador que lo registra.
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Permite consultar los implementos incluidos en el préstamo.
    public function loanItems(): HasMany
    {
        return $this->hasMany(LoanItem::class);
    }
}