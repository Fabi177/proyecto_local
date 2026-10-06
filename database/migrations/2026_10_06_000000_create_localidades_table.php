<?php

use Database\Seeders\LocalidadSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Catálogo de localidades: cada comercio elige una (ver la migración que agrega
        // comercios.localidad_id) y el buscador público filtra por ella.
        Schema::create('localidades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->string('provincia', 60)->default('Misiones');
            $table->string('codigo_postal', 8);
            // Nombre en minúsculas y sin tildes ("oberá" => "obera"), para que el autocompletado
            // encuentre igual en MySQL y en SQLite sin importar cómo escriba la persona.
            $table->string('nombre_busqueda', 120)->index();
            $table->timestamps();

            $table->unique(['nombre', 'provincia']);
            $table->index('codigo_postal');
        });

        // Se carga el catálogo inicial (es idempotente: se puede volver a correr sin duplicar).
        (new LocalidadSeeder)->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('localidades');
    }
};
