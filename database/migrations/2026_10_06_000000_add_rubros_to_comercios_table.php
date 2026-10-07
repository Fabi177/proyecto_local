<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rubros viejos (los del desplegable de una sola opción) que se parten en más de uno.
     * El que no aparece acá conserva su clave: ya existe igual en el catálogo nuevo.
     *
     * Está escrito acá adentro a propósito: una migración tiene que seguir funcionando
     * aunque el catálogo (App\Support\Rubros) cambie más adelante.
     */
    private const DESDOBLAR = [
        'Cafe' => ['Cafe', 'BarPub'],                          // "Cafetería / Bar"
        'Panaderia' => ['Panaderia', 'Pasteleria'],            // "Panadería / Pastelería"
        'Supermercado' => ['Supermercado', 'Almacen'],         // "Supermercado / Almacén"
        'Carniceria' => ['Carniceria', 'Pescaderia'],          // "Carnicería / Pescadería"
        'Peluqueria' => ['Peluqueria', 'Barberia'],            // "Peluquería / Barbería"
        'Mecanico' => ['Mecanico', 'RepuestosAuto'],           // "Taller Mecánico / Repuestos"
        'Lavanderia' => ['Lavanderia', 'Tintoreria'],          // "Lavandería / Tintorería"
        'Libreria' => ['Libreria', 'Artistica'],               // "Librería / Artística"
        'Hogar' => ['Hogar', 'Decoracion', 'Muebles'],         // "Hogar / Decoración / Muebles"
        'Mascotas' => ['Veterinaria', 'PetShop'],              // "Veterinaria / Pet Shop"
        'Tecnologia' => ['Computacion', 'VentaTecnologia'],    // "Tecnología / Computación"
        'ServiciosProfesionales' => ['Abogado', 'Contador'],   // "Servicios Profesionales (Abogado, Contador)"
        'Hoteleria' => ['Hotel'],                              // "Hotelería / Turismo"
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comercios', function (Blueprint $table) {
            // Todos los rubros del comercio (lista de claves). "rubro" queda como el principal (el primero).
            $table->json('rubros')->nullable()->after('rubro');
        });

        // Los comercios que ya existían pasan a tener su lista de rubros.
        DB::table('comercios')->select('id', 'rubro')->orderBy('id')->chunkById(100, function ($filas) {
            foreach ($filas as $fila) {
                $clave = trim((string) $fila->rubro);
                $rubros = array_values(array_unique(array_filter(self::DESDOBLAR[$clave] ?? [$clave])));

                DB::table('comercios')->where('id', $fila->id)->update([
                    'rubros' => json_encode($rubros, JSON_UNESCAPED_UNICODE),
                    'rubro' => $rubros[0] ?? $fila->rubro,
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * Se quita la lista de rubros. La columna "rubro" conserva el rubro principal que tenía cada
     * comercio (ya no el texto combinado de antes, por ejemplo "Cafetería / Bar").
     */
    public function down(): void
    {
        Schema::table('comercios', function (Blueprint $table) {
            $table->dropColumn('rubros');
        });
    }
};
