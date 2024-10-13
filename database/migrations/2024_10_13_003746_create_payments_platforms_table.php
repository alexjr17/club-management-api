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
        Schema::create('pagos_plataforma', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('suscripcion_id');
            $table->float('monto');
            $table->date('fecha_pago');
            $table->enum('estado', ['pendiente', 'completado', 'fallido']);
            $table->string('referencia_pago')->nullable();
            $table->timestamps();

            $table->foreign('suscripcion_id')->references('id')->on('suscripciones');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos_plataforma');
    }
};
