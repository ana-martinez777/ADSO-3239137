<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids; // genera identificadores UUID v4
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, HasVersion4Uuids, Notifiable;

    protected $authPasswordName = 'password_hash'; // indica a Laravel dónde consultar la contraseña

    protected $fillable = [ // permite asignar los tres atributos definidos en el modelo.
        'username',
        'password_hash',
        'role',
    ];

    protected $hidden = [ // evita exponer el hash cuando el usuario se convierte a JSON.
        'password_hash',
    ];

    protected function casts(): array // utiliza el mecanismo de hashing de Laravel, configurado con Argon2id.
    {
        return [
            'password_hash' => 'hashed',
        ];
    }

    // Permite consultar los préstamos registrados por el entrenador.
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }
}