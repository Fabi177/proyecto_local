<?php

namespace Database\Seeders;

use App\Models\Localidad;
use Illuminate\Database\Seeder;

/**
 * Catálogo de localidades de Misiones con su código postal.
 *
 * Es idempotente: se puede correr todas las veces que haga falta
 * (php artisan db:seed --class=LocalidadSeeder) y solo agrega o actualiza, nunca duplica
 * ni borra. Para sumar una localidad, agregarla a la lista de abajo y volver a correrlo.
 *
 * Los códigos postales salen de un listado público (GeoNames); conviene revisarlos.
 * Hay códigos compartidos por varias localidades (por ejemplo 3364 o 3361): es normal.
 */
class LocalidadSeeder extends Seeder
{
    private const PROVINCIA = 'Misiones';

    /** nombre => código postal */
    private const LOCALIDADES = [
        // Departamento Leandro N. Alem
        'Leandro N. Alem' => '3315',
        'Arroyo del Medio' => '3313',
        'Cerro Azul' => '3313',
        'Dos Arroyos' => '3315',
        'Olegario V. Andrade' => '3311',

        // Resto de Misiones
        '25 de Mayo' => '3363',
        'Alba Posse' => '3363',
        'Apóstoles' => '3350',
        'Aristóbulo del Valle' => '3364',
        'Azara' => '3351',
        'Bernardo de Irigoyen' => '3366',
        'Campo Grande' => '3362',
        'Campo Viera' => '3362',
        'Candelaria' => '3308',
        'Capioví' => '3332',
        'Colonia Aurora' => '3363',
        'Colonia Wanda' => '3376',
        'Concepción de la Sierra' => '3355',
        'Corpus' => '3327',
        'Eldorado' => '3380',
        'El Soberbio' => '3364',
        'Garupá' => '3304',
        'Gobernador Roca' => '3324',
        'Guaraní' => '3361',
        'Jardín América' => '3328',
        'Libertador General San Martín' => '3334',
        'Montecarlo' => '3384',
        'Oberá' => '3360',
        'Panambí' => '3361',
        'Posadas' => '3300',
        'Puerto Esperanza' => '3378',
        'Puerto Iguazú' => '3370',
        'Puerto Piray' => '3381',
        'Puerto Rico' => '3334',
        'Ruiz de Montoya' => '3334',
        'San Ignacio' => '3322',
        'San Javier' => '3357',
        'San José' => '3306',
        'San Pedro' => '3364',
        'San Vicente' => '3364',
        'Santa Ana' => '3316',
        'Santo Pipó' => '3326',
    ];

    public function run(): void
    {
        $filas = [];
        foreach (self::LOCALIDADES as $nombre => $codigoPostal) {
            $filas[] = [
                'nombre' => $nombre,
                'provincia' => self::PROVINCIA,
                'codigo_postal' => $codigoPostal,
                'nombre_busqueda' => Localidad::normalizar($nombre),
            ];
        }

        // upsert no dispara los eventos del modelo, por eso nombre_busqueda se calcula acá.
        Localidad::upsert($filas, ['nombre', 'provincia'], ['codigo_postal', 'nombre_busqueda']);
    }
}
