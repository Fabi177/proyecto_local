<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "habilitado": si el comercio se muestra al público (buscador, sugerencias y perfil).
     * Todos los comercios existentes quedan habilitados.
     */
    public function up(): void
    {
        Schema::table('comercios', function (Blueprint $table) {
            $table->boolean('habilitado')->default(true)->after('rubro')->index();
        });
    }

    public function down(): void
    {
        Schema::table('comercios', function (Blueprint $table) {
            $table->dropIndex(['habilitado']);
            $table->dropColumn('habilitado');
        });
    }
};
