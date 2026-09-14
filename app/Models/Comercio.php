<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Importante para la relación

class Comercio extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'nombre',
        'direccion',
        'telefono',
        'descripcion',
        'rubro',
        'horarios_atencion',
        'dias_no_laborales',
        'ingreso_discapacitados',
        'estacionamiento',
        'servicios_adicionales',
        'formas_pago',
        'sitio_web',
        'red_instagram',
        'red_facebook',
        'red_whatsapp',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ingreso_discapacitados' => 'boolean',
            'estacionamiento' => 'boolean',
        ];
    }

    /**
     * Define la relación: Un Comercio pertenece a un Usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
