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

    // The table does not use created_at or updated_at.
    public $timestamps = false;

    protected $authPasswordName = 'password_hash'; // tells Laravel which column holds the password

    protected $fillable = [ // allows mass assignment of the three model attributes.
        'username',
        'password_hash',
        'role',
    ];

    protected $hidden = [ // keeps the hash out of the JSON representation of the user.
        'password_hash',
    ];

    protected function casts(): array // uses Laravel's hashing, configured with Argon2id.
    {
        return [
            'password_hash' => 'hashed',
        ];
    }

    // Loans registered by the coach.
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }
}