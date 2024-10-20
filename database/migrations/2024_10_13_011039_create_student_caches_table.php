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
        Schema::create('alumno_cache', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rol_usuario_id');
            $table->unsignedBigInteger('padre_rol_usuario_id')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Agrega el campo deleted_at para eliminaciones suaves

            $table->foreign('rol_usuario_id')->references('id')->on('roles_usuarios');
            $table->foreign('padre_rol_usuario_id')->references('id')->on('roles_usuarios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumno_cache');
    }
};
