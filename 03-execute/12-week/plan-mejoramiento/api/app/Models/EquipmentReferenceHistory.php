<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentReferenceHistory extends Model
{
    // Exact table name in MySQL.
    protected $table = 'equipment_reference_history';

    // The primary key is the reference.
    protected $primaryKey = 'reference';

    // The primary key is a string.
    protected $keyType = 'string';

    // The primary key is not auto-generated.
    public $incrementing = false;

    // Fields that can be mass-assigned.
    protected $fillable = [
        'reference',
        'equipment_id',
    ];

    // The table does not use created_at or updated_at.
    public $timestamps = false;

    // The equipment this historical reference belongs to.
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }
}