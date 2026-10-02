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
     * Rubros disponibles, agrupados (valor guardado => etiqueta que se muestra).
     *
     * Son los mismos que ofrece el campo "Rubro / Categoría *" del formulario del
     * comerciante. Los usa el filtro por rubro del dashboard público.
     */
    public const RUBROS = [
        'Gastronomía' => [
            'Restaurante' => 'Restaurante',
            'Cafe' => 'Cafetería / Bar',
            'Panaderia' => 'Panadería / Pastelería',
            'Supermercado' => 'Supermercado / Almacén',
            'Verduleria' => 'Verdulería / Frutería',
            'Carniceria' => 'Carnicería / Pescadería',
            'Delivery' => 'Solo Delivery',
        ],
        'Tiendas y Compras' => [
            'Indumentaria' => 'Indumentaria y Accesorios',
            'Calzado' => 'Zapatería',
            'Tecnologia' => 'Tecnología / Computación',
            'Hogar' => 'Hogar / Decoración / Muebles',
            'Libreria' => 'Librería / Artística',
            'Jugueteria' => 'Juguetería',
            'Ferreteria' => 'Ferretería',
            'Kiosco' => 'Kiosco / Drugstore',
        ],
        'Salud y Bienestar' => [
            'Farmacia' => 'Farmacia',
            'Optica' => 'Óptica',
            'Gimnasio' => 'Gimnasio / Fitness',
            'Peluqueria' => 'Peluquería / Barbería',
            'Estetica' => 'Belleza / Estética',
        ],
        'Servicios y Profesionales' => [
            'Mecanico' => 'Taller Mecánico / Repuestos',
            'Mascotas' => 'Veterinaria / Pet Shop',
            'Lavanderia' => 'Lavandería / Tintorería',
            'ReparacionesHogar' => 'Reparaciones del Hogar (Plomería, etc.)',
            'ServiciosProfesionales' => 'Servicios Profesionales (Abogado, Contador)',
        ],
        'Ocio y Otros' => [
            'Hoteleria' => 'Hotelería / Turismo',
            'Entretenimiento' => 'Entretenimiento',
            'Otro' => 'Otro',
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
     * Reseñas (calificación + comentario) que dejaron los clientes.
     */
    public function resenas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Resena::class);
    }
}
