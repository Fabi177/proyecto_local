<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Localidad extends Model
{
    /** Laravel pluralizaría "Localidad" como "localidads"; la tabla se llama "localidades". */
    protected $table = 'localidades';

    protected $fillable = [
        'nombre',
        'provincia',
        'codigo_postal',
        'nombre_busqueda',
    ];

    /**
     * Deja un texto en minúsculas, sin tildes y con los espacios prolijos:
     * "  Oberá  " => "obera". Se usa para guardar nombre_busqueda y para lo que escribe la persona.
     */
    public static function normalizar(string $texto): string
    {
        $texto = preg_replace('/\s+/u', ' ', $texto) ?? '';

        return Str::ascii(mb_strtolower(trim($texto)));
    }

    protected static function booted(): void
    {
        // Si se crea o edita una localidad con el modelo, el texto de búsqueda se mantiene solo.
        static::saving(function (Localidad $localidad) {
            $localidad->nombre_busqueda = static::normalizar((string) $localidad->nombre);
        });
    }

    /**
     * Texto para mostrar: "Leandro N. Alem (3315)".
     */
    public function getEtiquetaAttribute(): string
    {
        return "{$this->nombre} ({$this->codigo_postal})";
    }

    /**
     * Comercios que están en esta localidad.
     */
    public function comercios(): HasMany
    {
        return $this->hasMany(Comercio::class);
    }
}
