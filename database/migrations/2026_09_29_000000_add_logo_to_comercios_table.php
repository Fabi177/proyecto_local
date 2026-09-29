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
            // Ruta relativa del logo dentro del disco "public" (ej: logos/abc123.png).
            // Es nullable porque el logo es opcional y los comercios ya existentes no tienen.
            $table->string('logo')->nullable()->after('rubro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comercios', function (Blueprint $table) {
            $table->dropColumn('logo');
        });
    }
};
