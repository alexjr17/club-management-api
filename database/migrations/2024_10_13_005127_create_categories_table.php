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
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id');
            $table->unsignedBigInteger('deporte_id');
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->string('referencia')->unique();
            $table->timestamps();
            $table->softDeletes(); // Agrega el campo deleted_at para eliminaciones suaves

            $table->foreign('club_id')->references('id')->on('clubes');
            $table->foreign('deporte_id')->references('id')->on('deportes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
