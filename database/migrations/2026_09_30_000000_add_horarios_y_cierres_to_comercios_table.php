<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Los textos legibles (horarios_atencion, dias_no_laborales, formas_pago) se siguen guardando
     * como antes, para mostrarlos en el perfil. Las columnas nuevas guardan la versión ESTRUCTURADA
     * (JSON) para poder volver a cargar el editor de horarios y de fechas de cierre al editar.
     */
    public function up(): void
    {
        Schema::table('comercios', function (Blueprint $table) {
            // Un resumen con 7 días distintos y dos turnos por día supera los 255 caracteres de string().
            $table->text('horarios_atencion')->nullable()->change();
            $table->text('dias_no_laborales')->nullable()->change();
            $table->text('formas_pago')->nullable()->change();
        });

        Schema::table('comercios', function (Blueprint $table) {
            // Horario por día: [{"open": true, "t": [["09:00","18:00"]]}, ...] (7 elementos, lunes a domingo)
            $table->json('horarios_config')->nullable()->after('horarios_atencion');
            // Fechas puntuales de cierre: [{"a": "2026-12-24", "b": "2026-12-26", "w": "Vacaciones"}, ...]
            $table->json('dias_cierre')->nullable()->after('dias_no_laborales');
            // "Cerramos los feriados nacionales"
            $table->boolean('cierra_feriados')->default(false)->after('dias_cierre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comercios', function (Blueprint $table) {
            $table->dropColumn(['horarios_config', 'dias_cierre', 'cierra_feriados']);
        });

        Schema::table('comercios', function (Blueprint $table) {
            // Ojo: al volver a string(255), MySQL puede cortar los textos que superen ese largo.
            $table->string('horarios_atencion')->nullable()->change();
            $table->string('dias_no_laborales')->nullable()->change();
            $table->string('formas_pago')->nullable()->change();
        });
    }
};
