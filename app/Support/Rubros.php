<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Catálogo de rubros en dos niveles: categoría principal → rubros.
 *
 * Lo usan el filtro del dashboard público y el formulario del comerciante (que puede
 * elegir TODOS los rubros que quiera). En la base se guarda la CLAVE de cada rubro
 * (ej: "Gomeria"); la etiqueta ("Gomería") es solo para mostrar.
 *
 * Para sumar un rubro: agregar una línea "Clave" => "Etiqueta" en la categoría que
 * corresponda. Reglas:
 *  - la clave es única en TODO el catálogo (un rubro vive en una sola categoría);
 *  - la clave no se cambia una vez en uso (los comercios guardan la clave);
 *  - sin tildes ni espacios en la clave.
 */
class Rubros
{
    public const CATEGORIAS = [
        'Gastronomía' => [
            'icono' => '🍔',
            'items' => [
                'Restaurante' => 'Restaurante',
                'BarPub' => 'Bar / Pub',
                'Cafe' => 'Cafetería',
                'Pizzeria' => 'Pizzería',
                'ComidaRapida' => 'Comida rápida',
                'Heladeria' => 'Heladería',
                'Panaderia' => 'Panadería',
                'Pasteleria' => 'Pastelería',
                'Rotiseria' => 'Rotisería',
                'Parrilla' => 'Parrilla',
                'ComidaSaludable' => 'Comida saludable',
                'Delivery' => 'Delivery / Take Away',
                'Supermercado' => 'Supermercado',
                'Autoservicio' => 'Autoservicio',
                'Almacen' => 'Almacén',
                'Kiosco' => 'Kiosco / Maxikiosco',
                'Carniceria' => 'Carnicería',
                'Pescaderia' => 'Pescadería',
                'Verduleria' => 'Verdulería / Frutería',
                'Fiambreria' => 'Fiambrería',
                'Dietetica' => 'Dietética',
                'Vinoteca' => 'Vinoteca / Bebidas',
                'DistribuidoraAlimentos' => 'Distribuidora de alimentos',
            ],
        ],

        'Compras' => [
            'icono' => '🛍️',
            'items' => [
                'Indumentaria' => 'Indumentaria',
                'RopaDeportiva' => 'Ropa deportiva',
                'RopaInfantil' => 'Ropa infantil / bebés',
                'Calzado' => 'Calzado',
                'Marroquineria' => 'Marroquinería',
                'Accesorios' => 'Accesorios / Bijouterie',
                'Joyeria' => 'Joyería / Relojería',
                'Perfumeria' => 'Perfumería',
                'Cosmetica' => 'Cosmética',
                'Jugueteria' => 'Juguetería',
                'Libreria' => 'Librería',
                'Artistica' => 'Artística',
                'Regaleria' => 'Regalería',
                'Bazar' => 'Bazar',
                'Hogar' => 'Hogar',
                'Decoracion' => 'Decoración',
                'Muebles' => 'Muebles',
                'Colchones' => 'Colchones',
                'Electrodomesticos' => 'Electrodomésticos',
                'Ferreteria' => 'Ferretería',
                'Pintureria' => 'Pinturería',
                'Vivero' => 'Vivero / Jardinería',
                'Floreria' => 'Florería',
            ],
        ],

        'Automotores' => [
            'icono' => '🚗',
            'items' => [
                'Concesionaria' => 'Concesionaria',
                'CompraVentaVehiculos' => 'Compra / venta de vehículos',
                'Mecanico' => 'Taller mecánico',
                'ElectricidadAutomotor' => 'Electricidad del automotor',
                'ChapaPintura' => 'Chapa y pintura',
                'Gomeria' => 'Gomería',
                'Lubricentro' => 'Lubricentro',
                'RepuestosAuto' => 'Repuestos de autos',
                'AccesoriosAuto' => 'Accesorios para autos',
                'LavaderoAutos' => 'Lavadero de autos',
                'Detailing' => 'Detailing',
                'Polarizado' => 'Polarizado',
                'AlarmasGps' => 'Alarmas / GPS',
                'Motos' => 'Motos',
                'RepuestosMotos' => 'Repuestos de motos',
                'TallerMotos' => 'Taller de motos',
                'Bicicletas' => 'Bicicletas',
            ],
        ],

        'Hogar y construcción' => [
            'icono' => '🏠',
            'items' => [
                'Plomeria' => 'Plomería',
                'Electricidad' => 'Electricidad',
                'Gasista' => 'Gasista',
                'Albanileria' => 'Albañilería',
                'Pintura' => 'Pintura',
                'Yeseria' => 'Yesería / Durlock',
                'Carpinteria' => 'Carpintería',
                'Herreria' => 'Herrería',
                'Techos' => 'Techos',
                'Vidrieria' => 'Vidriería',
                'Cerrajeria' => 'Cerrajería',
                'AireAcondicionado' => 'Aire acondicionado',
                'Refrigeracion' => 'Refrigeración',
                'Calefaccion' => 'Calefacción',
                'Sanitarios' => 'Sanitarios',
                'MaterialesConstruccion' => 'Materiales de construcción',
                'Corralon' => 'Corralón',
                'Maderera' => 'Maderera',
                'Aberturas' => 'Aberturas',
                'ReparacionesHogar' => 'Reparaciones del hogar',
            ],
        ],

        'Tecnología' => [
            'icono' => '💻',
            'items' => [
                'Computacion' => 'Computación',
                'ReparacionPC' => 'Reparación de PC',
                'ReparacionCelulares' => 'Reparación de celulares',
                'Telefonia' => 'Telefonía',
                'AccesoriosCelulares' => 'Accesorios para celulares',
                'Electronica' => 'Electrónica',
                'ReparacionElectronica' => 'Reparación electrónica',
                'Impresoras' => 'Impresoras',
                'Redes' => 'Redes / Networking',
                'Internet' => 'Internet',
                'Telecomunicaciones' => 'Telecomunicaciones',
                'CamarasSeguridad' => 'Cámaras de seguridad',
                'Alarmas' => 'Alarmas',
                'Domotica' => 'Domótica',
                'VentaTecnologia' => 'Venta de tecnología',
            ],
        ],

        'Salud' => [
            'icono' => '🏥',
            'items' => [
                'Farmacia' => 'Farmacia',
                'Optica' => 'Óptica',
                'Odontologia' => 'Odontología',
                'Clinica' => 'Clínica',
                'Hospital' => 'Hospital',
                'ConsultorioMedico' => 'Consultorio médico',
                'Laboratorio' => 'Laboratorio',
                'Kinesiologia' => 'Kinesiología',
                'Fonoaudiologia' => 'Fonoaudiología',
                'Psicologia' => 'Psicología',
                'Nutricion' => 'Nutrición',
                'Enfermeria' => 'Enfermería',
                'DiagnosticoImagenes' => 'Diagnóstico por imágenes',
                'Fisioterapia' => 'Fisioterapia',
                'Ortopedia' => 'Ortopedia',
            ],
        ],

        'Belleza' => [
            'icono' => '💇',
            'items' => [
                'Peluqueria' => 'Peluquería',
                'Barberia' => 'Barbería',
                'Estetica' => 'Estética',
                'Spa' => 'Spa',
                'Manicuria' => 'Manicuría',
                'Pedicuria' => 'Pedicuría',
                'Depilacion' => 'Depilación',
                'Maquillaje' => 'Maquillaje',
                'Masajes' => 'Masajes',
                'Tatuajes' => 'Tatuajes / Piercing',
            ],
        ],

        'Mascotas' => [
            'icono' => '🐶',
            'items' => [
                'Veterinaria' => 'Veterinaria',
                'PetShop' => 'Pet Shop',
                'PeluqueriaCanina' => 'Peluquería canina',
                'Adiestramiento' => 'Adiestramiento',
                'GuarderiaMascotas' => 'Guardería de mascotas',
                'PaseadorPerros' => 'Paseador de perros',
            ],
        ],

        'Servicios' => [
            'icono' => '⚙️',
            'items' => [
                'Limpieza' => 'Limpieza',
                'Lavanderia' => 'Lavandería',
                'Tintoreria' => 'Tintorería',
                'ReparacionElectrodomesticos' => 'Reparación de electrodomésticos',
                'ReparacionHerramientas' => 'Reparación de herramientas',
                'Fotografia' => 'Fotografía',
                'Video' => 'Video',
                'Imprenta' => 'Imprenta',
                'Grafica' => 'Gráfica',
                'Diseno' => 'Diseño',
                'Publicidad' => 'Publicidad',
                'Marketing' => 'Marketing',
                'Eventos' => 'Organización de eventos',
                'Catering' => 'Catering',
                'SeguridadPrivada' => 'Seguridad privada',
            ],
        ],

        'Profesionales' => [
            'icono' => '👔',
            'items' => [
                'Abogado' => 'Abogado',
                'Contador' => 'Contador',
                'Escribano' => 'Escribano',
                'Arquitecto' => 'Arquitecto',
                'Ingeniero' => 'Ingeniero',
                'Agrimensor' => 'Agrimensor',
                'Programador' => 'Programador / Desarrollador',
                'Consultor' => 'Consultor',
                'RecursosHumanos' => 'Recursos humanos',
                'Gestoria' => 'Gestoría',
                'Administracion' => 'Administración',
            ],
        ],

        'Agro' => [
            'icono' => '🚜',
            'items' => [
                'Agropecuaria' => 'Agropecuaria',
                'VeterinariaRural' => 'Veterinaria rural',
                'Semilleria' => 'Semillería',
                'InsumosAgricolas' => 'Insumos agrícolas',
                'MaquinariaAgricola' => 'Maquinaria agrícola',
                'RepuestosAgricolas' => 'Repuestos agrícolas',
                'Forrajes' => 'Forrajes',
                'AlimentosAnimales' => 'Alimentos para animales',
                'FerreteriaRural' => 'Ferretería rural',
                'Forestal' => 'Forestal',
            ],
        ],

        'Transporte' => [
            'icono' => '🚛',
            'items' => [
                'Remis' => 'Remis',
                'Taxi' => 'Taxi',
                'TransportePasajeros' => 'Transporte de pasajeros',
                'Fletes' => 'Fletes',
                'Mudanzas' => 'Mudanzas',
                'Logistica' => 'Logística',
                'Mensajeria' => 'Mensajería',
                'Correo' => 'Correo',
                'TransporteCargas' => 'Transporte de cargas',
                'AlquilerVehiculos' => 'Alquiler de vehículos',
                'Estacionamiento' => 'Estacionamiento',
            ],
        ],

        'Turismo' => [
            'icono' => '🏨',
            'items' => [
                'Hotel' => 'Hotel',
                'Hostel' => 'Hostel',
                'Cabanas' => 'Cabañas',
                'ApartHotel' => 'Apart hotel',
                'Camping' => 'Camping',
                'AlquilerTuristico' => 'Alquiler turístico',
                'AgenciaViajes' => 'Agencia de viajes',
                'Excursiones' => 'Excursiones',
                'GuiasTuristicos' => 'Guías turísticos',
            ],
        ],

        'Entretenimiento' => [
            'icono' => '🎮',
            'items' => [
                'Gimnasio' => 'Gimnasio',
                'ClubDeportivo' => 'Club deportivo',
                'Cancha' => 'Cancha',
                'Futbol' => 'Fútbol',
                'Padel' => 'Pádel',
                'Bowling' => 'Bowling',
                'Juegos' => 'Juegos',
                'Cine' => 'Cine',
                'Teatro' => 'Teatro',
                'SalonFiestas' => 'Salón de fiestas',
                'ParqueRecreativo' => 'Parque recreativo',
                'Discoteca' => 'Discoteca / Boliche',
                'Entretenimiento' => 'Otros entretenimientos',
            ],
        ],

        'Educación' => [
            'icono' => '🎓',
            'items' => [
                'Escuela' => 'Escuela',
                'Colegio' => 'Colegio',
                'Universidad' => 'Universidad',
                'InstitutoTerciario' => 'Instituto terciario',
                'Academia' => 'Academia',
                'Cursos' => 'Cursos',
                'Idiomas' => 'Idiomas',
                'ApoyoEscolar' => 'Apoyo escolar',
                'Capacitacion' => 'Capacitación profesional',
                'MusicaArte' => 'Música / Arte',
            ],
        ],

        'Finanzas' => [
            'icono' => '💰',
            'items' => [
                'Banco' => 'Banco',
                'CajeroAutomatico' => 'Cajero automático',
                'Cooperativa' => 'Cooperativa',
                'Financiera' => 'Financiera',
                'Seguros' => 'Seguros',
                'CasaCambio' => 'Casa de cambio',
                'PagoFacil' => 'Pago Fácil',
                'Rapipago' => 'Rapipago',
            ],
        ],

        'Inmobiliaria' => [
            'icono' => '🏢',
            'items' => [
                'Inmobiliaria' => 'Inmobiliaria',
                'Alquileres' => 'Alquileres',
                'VentaPropiedades' => 'Venta de propiedades',
                'AdministracionPropiedades' => 'Administración de propiedades',
                'Loteos' => 'Loteos',
                'DesarrollosInmobiliarios' => 'Desarrollos inmobiliarios',
            ],
        ],

        'Combustibles' => [
            'icono' => '⛽',
            'items' => [
                'EstacionServicio' => 'Estación de servicio',
                'GNC' => 'GNC',
                'Combustibles' => 'Combustibles',
                'Lubricantes' => 'Lubricantes',
            ],
        ],

        'Instituciones' => [
            'icono' => '🏛️',
            'items' => [
                'Municipalidad' => 'Municipalidad',
                'OrganismosPublicos' => 'Organismos públicos',
                'Policia' => 'Policía',
                'Bomberos' => 'Bomberos',
                'Iglesias' => 'Iglesias / templos',
                'Asociaciones' => 'Asociaciones',
                'ONG' => 'ONG',
                'Sindicatos' => 'Sindicatos',
                'Clubes' => 'Clubes',
            ],
        ],

        // Para el comercio que no encaja en ninguna de las anteriores.
        'Otros' => [
            'icono' => '📦',
            'items' => [
                'Otro' => 'Otro',
            ],
        ],
    ];

    /** Caché de la lista plana (clave => etiqueta). */
    private static ?array $planos = null;

    /** Caché clave normalizada => clave. */
    private static ?array $porNormalizada = null;

    /**
     * Categorías con su ícono y sus rubros: nombre => ['icono' => ..., 'items' => [clave => etiqueta]].
     */
    public static function categorias(): array
    {
        return self::CATEGORIAS;
    }

    /**
     * Todos los rubros en una sola lista: clave => etiqueta.
     */
    public static function planos(): array
    {
        if (self::$planos === null) {
            self::$planos = [];

            foreach (self::CATEGORIAS as $categoria) {
                self::$planos = array_merge(self::$planos, $categoria['items']);
            }
        }

        return self::$planos;
    }

    /**
     * Las claves de todos los rubros.
     *
     * @return array<int, string>
     */
    public static function claves(): array
    {
        return array_keys(self::planos());
    }

    /**
     * Para el filtro y el formulario: categoría => [claves de sus rubros].
     *
     * @return array<string, array<int, string>>
     */
    public static function clavesPorCategoria(): array
    {
        $resultado = [];

        foreach (self::CATEGORIAS as $nombre => $categoria) {
            $resultado[$nombre] = array_keys($categoria['items']);
        }

        return $resultado;
    }

    /**
     * Etiqueta para mostrar. Si la clave no está en el catálogo (dato viejo escrito a mano)
     * se muestra tal cual, así nunca queda un comercio sin rubro visible.
     */
    public static function etiqueta(string $clave): string
    {
        return self::planos()[$clave] ?? $clave;
    }

    /**
     * Etiquetas de una lista de claves, en el mismo orden.
     *
     * @param  array<int, string>  $claves
     * @return array<int, string>
     */
    public static function etiquetas(array $claves): array
    {
        return array_values(array_map(fn ($clave) => self::etiqueta((string) $clave), $claves));
    }

    /**
     * Lleva lo que llegó por la URL a la clave del catálogo sin importar mayúsculas ni tildes
     * (?rubro=cafe -> "Cafe"). Si no coincide con ninguna, devuelve el texto recortado tal cual.
     */
    public static function canonica(string $valor): string
    {
        if (self::$porNormalizada === null) {
            self::$porNormalizada = [];

            foreach (self::claves() as $clave) {
                self::$porNormalizada[self::normalizar($clave)] = $clave;
            }
        }

        return self::$porNormalizada[self::normalizar($valor)] ?? trim($valor);
    }

    /**
     * Claves de los rubros cuya etiqueta (o clave) contiene el texto buscado,
     * sin importar mayúsculas ni tildes: "pizz" -> ["Pizzeria"].
     *
     * @return array<int, string>
     */
    public static function clavesQueCoinciden(string $texto): array
    {
        $buscado = self::normalizar($texto);

        if ($buscado === '') {
            return [];
        }

        $coinciden = [];

        foreach (self::planos() as $clave => $etiqueta) {
            if (str_contains(self::normalizar($etiqueta), $buscado) || str_contains(self::normalizar($clave), $buscado)) {
                $coinciden[] = $clave;
            }
        }

        return $coinciden;
    }

    /**
     * Minúsculas, sin tildes y con los espacios juntos: " Ferretería  Rural " -> "ferreteria rural".
     */
    private static function normalizar(string $texto): string
    {
        return trim(preg_replace('/\s+/u', ' ', Str::lower(Str::ascii($texto))) ?? '');
    }
}
