<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id(); // Clave primaria
            $table->unsignedBigInteger('user_id'); // ID del usuario
            $table->unsignedBigInteger('accion_id')->nullable(); // ID de la acción (puede ser nulo)
            $table->string('accion'); // Acción realizada
            $table->string('modulo'); // Módulo en el que se realizó la acción
            $table->text('detalles')->nullable(); // Detalles adicionales
            $table->ipAddress('ip_address'); // Dirección IP
            $table->string('user_agent'); // Agente del usuario
            $table->boolean('mobile'); // Si es un dispositivo móvil
            $table->timestamps(); // Timestamps para created_at y updated_at
            $table->softDeletes(); // Para eliminaciones suaves (opcional)

            // Clave foránea para el campo user_id
            $table->foreign('user_id')->references('id')->on('usuarios')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logs');
    }
}
