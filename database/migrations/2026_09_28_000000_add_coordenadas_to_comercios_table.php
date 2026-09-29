<?php

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
        Schema::table('comercios', function (Blueprint $table) {
            // Geolocalización del comercio (opcional, por eso nullable).
            // decimal(10, 7): cubre latitud (-90 a 90) y longitud (-180 a 180)
            // con ~1 cm de precisión.
            $table->decimal('latitud', 10, 7)->nullable()->after('direccion');
            $table->decimal('longitud', 10, 7)->nullable()->after('latitud');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comercios', function (Blueprint $table) {
            $table->dropColumn(['latitud', 'longitud']);
        });
    }
};
