<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentReferenceHistory extends Model
{
    // Indica el nombre exacto de la tabla en MySQL.
    protected $table = 'equipment_reference_history';

    // Indica que la clave primaria se llama reference.
    protected $primaryKey = 'reference';

    // La clave primaria es de tipo texto.
    protected $keyType = 'string';

    // La clave primaria no se genera automáticamente.
    public $incrementing = false;

    // Define los campos que pueden asignarse masivamente.
    protected $fillable = [
        'reference',
        'equipment_id',
    ];

    // La tabla no utiliza created_at ni updated_at.
    public $timestamps = false;

    // Relaciona la referencia histórica con su implemento.
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }
}