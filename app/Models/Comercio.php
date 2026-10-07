<?php

namespace App\Models;

use App\Support\Rubros;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Importante para la relación
use Illuminate\Support\Facades\Storage;

class Comercio extends Model
{
    use HasFactory;

    /**
     * Accesibilidad y servicios del comercio (campo => etiquetas).
     *
     * - 'si':       etiqueta que se muestra cuando el comercio lo tiene.
     * - 'no':       etiqueta que se muestra cuando no lo tiene.
     * - 'pregunta': texto del checkbox en los formularios de alta y edición.
     *
     * Para sumar un ítem nuevo: agregar una columna booleana con una migración,
     * ponerla en $fillable y en casts(), y agregar una entrada acá. Las vistas
     * (perfil, alta y edición) y el controlador la toman de esta lista.
     */
    public const ACCESIBILIDAD = [
        'ingreso_discapacitados' => [
            'si' => 'Apto movilidad reducida',
            'no' => 'Sin acceso adaptado',
            'pregunta' => '¿Es apto para personas con movilidad reducida?',
        ],
        'rampa_acceso' => [
            'si' => 'Rampa de acceso',
            'no' => 'Entrada con escalones',
            'pregunta' => '¿Tiene rampa de acceso?',
        ],
        'estacionamiento' => [
            'si' => 'Estacionamiento exclusivo',
            'no' => 'Sin estacionamiento',
            'pregunta' => '¿Tiene estacionamiento exclusivo para clientes?',
        ],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'nombre',
        'direccion',
        'localidad_id',
        'latitud',
        'longitud',
        'logo',
        'telefono',
        'descripcion',
        'rubro',
        'rubros',
        'horarios_atencion',
        'horarios_config',
        'dias_no_laborales',
        'dias_cierre',
        'cierra_feriados',
        'ingreso_discapacitados',
        'rampa_acceso',
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
            'habilitado' => 'boolean',
            'rubros' => 'array',
            'ingreso_discapacitados' => 'boolean',
            'rampa_acceso' => 'boolean',
            'estacionamiento' => 'boolean',
            'cierra_feriados' => 'boolean',
            'horarios_config' => 'array',
            'dias_cierre' => 'array',
            'latitud' => 'float',
            'longitud' => 'float',
        ];
    }

    /**
     * Mantiene sincronizados "rubros" (la lista completa) y "rubro" (el principal = el primero).
     *
     * - Si se cargan "rubros", el primero pasa a ser "rubro".
     * - Si solo se cambia el texto "rubro" (código viejo, consola), pasa a ser el único rubro.
     */
    protected static function booted(): void
    {
        static::saving(function (Comercio $comercio) {
            $rubros = array_values(array_unique(array_filter(
                (array) $comercio->rubros,
                fn ($rubro) => is_string($rubro) && trim($rubro) !== ''
            )));

            if ($comercio->isDirty('rubro') && ! $comercio->isDirty('rubros')) {
                $rubros = filled($comercio->rubro) ? [trim($comercio->rubro)] : [];
            }

            if ($rubros !== []) {
                $comercio->rubros = $rubros;
                $comercio->rubro = $rubros[0];
            }
        });
    }

    /**
     * Etiquetas de todos los rubros del comercio, en orden: ["Cafetería", "Bar / Pub"].
     * Uso en las vistas: $comercio->rubros_etiquetas
     *
     * @return array<int, string>
     */
    public function getRubrosEtiquetasAttribute(): array
    {
        return Rubros::etiquetas($this->rubros ?: array_filter([$this->rubro]));
    }

    /**
     * Todos los rubros en un solo texto: "Cafetería, Bar / Pub, Pastelería".
     */
    public function getRubrosTextoAttribute(): string
    {
        return implode(', ', $this->rubros_etiquetas);
    }

    /**
     * Texto corto para listados: los primeros rubros y cuántos más hay ("Cafetería, Bar / Pub +2").
     */
    public function rubrosResumen(int $maximo = 2): string
    {
        $etiquetas = $this->rubros_etiquetas;
        $resumen = implode(', ', array_slice($etiquetas, 0, $maximo));

        return count($etiquetas) > $maximo ? $resumen . ' +' . (count($etiquetas) - $maximo) : $resumen;
    }

    /**
     * Comercios que tengan ALGUNO de los rubros indicados (claves).
     *
     * @param  array<int, string>  $claves
     */
    public function scopeConAlgunRubro(Builder $consulta, array $claves): Builder
    {
        $claves = array_values(array_filter(array_map('trim', $claves), fn ($clave) => $clave !== ''));

        if ($claves === []) {
            return $consulta;
        }

        return $consulta->where(function (Builder $q) use ($claves) {
            foreach ($claves as $clave) {
                $q->orWhereJsonContains('rubros', $clave);
            }

            // Red de seguridad: el rubro principal también cuenta (por si una fila no trae la lista).
            $marcas = implode(',', array_fill(0, count($claves), '?'));
            $q->orWhereRaw("LOWER(TRIM(rubro)) IN ({$marcas})", array_map('mb_strtolower', $claves));
        });
    }

    /**
     * Para usar DENTRO de un grupo de búsqueda por texto: suma los comercios que tengan un rubro
     * cuyo nombre contenga el texto ("pizz" encuentra a los de Pizzería).
     */
    public function scopeOrCoincideRubro(Builder $consulta, string $texto): Builder
    {
        foreach (Rubros::clavesQueCoinciden($texto) as $clave) {
            $consulta->orWhereJsonContains('rubros', $clave);
        }

        return $consulta;
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

    /**
     * Localidad donde está el comercio (puede ser null en comercios muy viejos).
     */
    public function localidad(): BelongsTo
    {
        return $this->belongsTo(Localidad::class);
    }

    /**
     * Reseñas (calificación + comentario) que dejaron los clientes.
     */
    public function resenas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Resena::class);
    }
}
