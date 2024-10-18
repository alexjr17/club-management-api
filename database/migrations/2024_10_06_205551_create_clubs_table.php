<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('clubes', function (Blueprint $table) {
            $table->id();
            $table->string('foto')->nullable();
            $table->string('nombre');
            $table->string('direccion');
            $table->string('descripcion');
            $table->string('barrio')->nullable();
            $table->string('nombre_ubicacion')->nullable();
            $table->string('correo');
            $table->string('telefono');
            $table->date('fecha_fundacion');
            $table->unsignedBigInteger('sede_id')->nullable();
            $table->unsignedBigInteger('usuario_admin_id');
            $table->string('ciudad');
            $table->string('database_connection')->nullable();
            $table->string('referencia')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Agrega el campo deleted_at para eliminaciones suaves

            $table->foreign('usuario_admin_id')->references('id')->on('usuarios');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clubes');
    }
};
