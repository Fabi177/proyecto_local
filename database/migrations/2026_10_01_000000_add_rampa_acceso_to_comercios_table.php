<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comercios', function (Blueprint $table) {
            // Accesibilidad: ¿el ingreso tiene rampa? (false = entrada con escalones)
            $table->boolean('rampa_acceso')->default(false)->after('ingreso_discapacitados');
        });
    }

    public function down(): void
    {
        Schema::table('comercios', function (Blueprint $table) {
            $table->dropColumn('rampa_acceso');
        });
    }
};