<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Importante para la relación
use Illuminate\Support\Facades\Storage;

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
        'latitud',
        'longitud',
        'logo',
        'telefono',
        'descripcion',
        'rubro',
        'horarios_atencion',
        'horarios_config',
        'dias_no_laborales',
        'dias_cierre',
        'cierra_feriados',
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
            'cierra_feriados' => 'boolean',
            'horarios_config' => 'array',
            'dias_cierre' => 'array',
            'latitud' => 'float',
            'longitud' => 'float',
        ];
    }

    /**
     * Indica si el comercio ya tiene su geolocalización cargada.
     */
    public function tieneUbicacion(): bool
    {
        return $this->latitud !== null && $this->longitud !== null;
    }

    /**
     * URL pública del logo, o null si el comercio no cargó ninguno.
     *
     * Se usa asset() y no Storage::url() a propósito: asset() toma el host y el puerto
     * de la petición actual (ej: localhost:8000), mientras que Storage::url() depende
     * de APP_URL y da enlaces rotos si ese valor no coincide con el puerto real.
     * Uso en las vistas: $comercio->logo_url
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo || !Storage::disk('public')->exists($this->logo)) {
            return null;
        }

        return asset('storage/' . $this->logo);
    }

    /**
     * Define la relación: Un Comercio pertenece a un Usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
