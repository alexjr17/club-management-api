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
        Schema::create('profesor_cache', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rol_usuario_id');
            $table->float('calificacion')->default(0);
            $table->integer('numero_calificaciones')->default(0);
            $table->text('filosofia')->nullable();
            $table->json('especializaciones')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Agrega el campo deleted_at para eliminaciones suaves
            $table->foreign('rol_usuario_id')->references('id')->on('roles_usuarios');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profesor_cache');
    }
};
