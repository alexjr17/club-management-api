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
        Schema::create('rol_personalizado_permiso', function (Blueprint $table) {
            $table->unsignedBigInteger('rol_personalizado_id');
            $table->unsignedBigInteger('permiso_personalizado_id');

            $table->primary(['rol_personalizado_id', 'permiso_personalizado_id']);
            $table->foreign('rol_personalizado_id')->references('id')->on('roles_personalizados');
            $table->foreign('permiso_personalizado_id')->references('id')->on('permisos_personalizados');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rol_personalizado_permiso');
    }
};
