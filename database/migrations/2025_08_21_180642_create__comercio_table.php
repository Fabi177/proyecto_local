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
        // CAMBIO: Renombrado de '_comercio' a 'comercios' (convención de Laravel)
        Schema::create('comercios', function (Blueprint $table) {
            $table->id();

            // --- VÍNCULO CON EL USUARIO (IMPORTANTE) ---
            // Esto conecta el comercio con el usuario que lo creó.
            // onDelete('cascade') borra el comercio si el usuario se elimina.
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // --- INFORMACIÓN BÁSICA ---
            $table->string('nombre'); // Nombre del Comercio
            $table->string('direccion'); // Dirección
            $table->string('telefono')->nullable(); // Teléfono (opcional)
            $table->text('descripcion')->nullable(); // Descripcion (texto largo, opcional)
            $table->string('rubro'); // Rubro/Categoria

            // --- HORARIOS ---
            $table->string('horarios_atencion')->nullable(); // ej. "Lunes a Viernes de 9 a 18"
            $table->string('dias_no_laborales')->nullable(); // ej. "Sábados y Domingos"

            // --- SERVICIOS Y ACCESIBILIDAD (Preguntas SI/NO) ---
            $table->boolean('ingreso_discapacitados')->default(false);
            $table->boolean('estacionamiento')->default(false);
            $table->text('servicios_adicionales')->nullable(); // "Si ofrece algún servicio"

            // --- PAGOS Y WEB ---
            $table->string('formas_pago')->nullable(); // ej. "Efectivo, Tarjeta, MP"
            $table->string('sitio_web')->nullable(); // "si tiene sitio web" (opcional)

            // --- REDES SOCIALES ---
            $table->string('red_instagram')->nullable();
            $table->string('red_facebook')->nullable();
            $table->string('red_whatsapp')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // CAMBIO: Asegurarse de que coincida con el nuevo nombre de la tabla
        Schema::dropIfExists('comercios');
    }
};
