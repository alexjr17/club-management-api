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
        // Schema::create('tbl_profesores', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('nombre');
        //     $table->string('apellido');
        //     $table->string('documento')->unique();
        //     $table->date('fecha_nacimiento');
        //     $table->string('foto')->nullable();
        //     $table->string('correo')->unique();
        //     $table->string('contraseña');
        //     $table->string('telefono')->nullable();
        //     $table->string('direccion')->nullable();
        //     // $table->date('fecha_contratacion')->nullable();
        //     $table->unsignedBigInteger('club_id')->nullable();
        //     $table->integer('años_experiencia')->default(0);
        //     $table->boolean('estado')->default(true);
        //     $table->boolean('tutorial_completado')->default(false);
        //     $table->float('calificacion_promedio', 2, 1)->default(0);
        //     $table->integer('numero_calificaciones')->default(0);
        //     $table->json('filosofia')->nullable();
        //     $table->json('deportes')->nullable();
        //     $table->json('especializaciones')->nullable();
        //     $table->timestamps();

        //     // Foreign key constraint
        //     $table->foreign('club_id')->references('id')->on('clubs')->onDelete('set null');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_profesores');
    }
};
