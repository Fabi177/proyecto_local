<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reseñas de los comercios: una calificación (1 a 5 estrellas) con comentario opcional.
     *
     * Cada cliente tiene UNA sola reseña por comercio (índice único): si vuelve a calificar,
     * se edita la que ya tenía. Las tablas viejas "_comentarios" y "_reseña" de las migraciones
     * de agosto eran borradores vacíos y no se tocan.
     */
    public function up(): void
    {
        Schema::create('resenas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comercio_id')->constrained('comercios')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('calificacion');
            $table->text('comentario')->nullable();
            $table->timestamps();

            $table->unique(['comercio_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resenas');
    }
};
