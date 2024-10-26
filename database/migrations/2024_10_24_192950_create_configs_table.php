<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('configuraciones', function (Blueprint $table) {

            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->foreignId('club_id')->nullable()->constrained('clubes');
            $table->enum('tipo', ['admin', 'usuario']);
            $table->string('modulo');
            $table->json('configuraciones');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Un usuario o club solo puede tener una configuración por módulo
            $table->unique(['usuario_id', 'modulo']);
            // $table->unique(['club_id', 'modulo']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('configuraciones');
    }

};
