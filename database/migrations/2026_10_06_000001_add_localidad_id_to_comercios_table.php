<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comercios', function (Blueprint $table) {
            // Localidad del comercio. Es opcional a nivel base de datos para no romper los
            // comercios que ya existen; el formulario y el servidor la exigen al crear o editar.
            // Si algún día se borra una localidad del catálogo, el comercio queda sin localidad.
            $table->foreignId('localidad_id')
                ->nullable()
                ->after('direccion')
                ->constrained('localidades')
                ->nullOnDelete();
        });

        // Los comercios cargados antes de esta migración se asignan a Leandro N. Alem
        // (donde arrancó el proyecto), así no desaparecen cuando alguien filtra por ciudad.
        // Si no querés esto, borrá este bloque antes de migrar y asignalas a mano desde la edición.
        $alem = DB::table('localidades')
            ->where('nombre', 'Leandro N. Alem')
            ->where('provincia', 'Misiones')
            ->value('id');

        if ($alem) {
            DB::table('comercios')->whereNull('localidad_id')->update(['localidad_id' => $alem]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comercios', function (Blueprint $table) {
            $table->dropConstrainedForeignId('localidad_id');
        });
    }
};
