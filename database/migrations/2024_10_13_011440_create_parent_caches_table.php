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
        Schema::create('padre_cache', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rol_usuario_id');
            $table->timestamp('last_updated');

            $table->foreign('rol_usuario_id')->references('id')->on('roles_usuarios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('padre_cache');
    }
};
