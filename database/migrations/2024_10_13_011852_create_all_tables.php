<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        return null;
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('foto')->nullable();
            $table->string('nombre');
            $table->string('apellido');
            $table->string('email')->unique();
            $table->string('password');
            $table->tinyInteger('estado')->default(1);
            $table->string('nombre_usuario')->unique();
            $table->string('telefono')->nullable();
            $table->string('ciudad')->nullable();
            $table->enum('tipo_documento', ['CC', 'TI', 'CE']);
            $table->string('numero_documento')->unique();
            $table->boolean('tutorial')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Agrega el campo deleted_at para eliminaciones suaves
        });
        Schema::create('clubes', function (Blueprint $table) {
            $table->id();
            $table->string('foto')->nullable();
            $table->string('nombre');
            $table->string('direccion');
            $table->string('barrio')->nullable();
            $table->string('nombre_ubicacion')->nullable();
            $table->string('correo');
            $table->string('telefono');
            $table->date('fecha_fundacion');
            $table->unsignedBigInteger('sede_id')->nullable();
            $table->unsignedBigInteger('usuario_admin_id');
            $table->string('ciudad');
            $table->string('database_connection');
            $table->string('referencia')->nullable();
            $table->timestamps();

            $table->foreign('usuario_admin_id')->references('id')->on('usuarios');
        });
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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->boolean('es_default')->default(false);
            $table->timestamps();

        });
        Schema::create('roles_personalizados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->unsignedBigInteger('club_id');
            $table->timestamps();

            $table->foreign('club_id')->references('id')->on('clubes');
        });
        Schema::create('roles_usuarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('rol_id')->nullable();
            $table->unsignedBigInteger('rol_personalizado_id')->nullable();
            $table->unsignedBigInteger('club_id');
            $table->timestamps();

            $table->foreign('usuario_id')->references('id')->on('usuarios');
            $table->foreign('rol_id')->references('id')->on('roles');
            $table->foreign('rol_personalizado_id')->references('id')->on('roles_personalizados');
            $table->foreign('club_id')->references('id')->on('clubes');
        });
        Schema::create('permisos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->string('referencia');
            $table->string('modulo');
            $table->string('acciones');
            $table->boolean('es_default')->default(false);
        });
        Schema::create('permisos_personalizados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->string('referencia');
            $table->string('modulo');
            $table->string('acciones');
            $table->unsignedBigInteger('club_id');
            $table->timestamps();

            $table->foreign('club_id')->references('id')->on('clubes');
        });
        Schema::create('rol_permiso', function (Blueprint $table) {
            $table->unsignedBigInteger('rol_id');
            $table->unsignedBigInteger('permiso_id');

            $table->primary(['rol_id', 'permiso_id']);
            $table->foreign('rol_id')->references('id')->on('roles');
            $table->foreign('permiso_id')->references('id')->on('permisos');
        });
        Schema::create('rol_personalizado_permiso', function (Blueprint $table) {
            $table->unsignedBigInteger('rol_personalizado_id');
            $table->unsignedBigInteger('permiso_personalizado_id');

            $table->primary(['rol_personalizado_id', 'permiso_personalizado_id']);
            $table->foreign('rol_personalizado_id')->references('id')->on('roles_personalizados');
            $table->foreign('permiso_personalizado_id')->references('id')->on('permisos_personalizados');
        });
        Schema::create('planes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->float('precio');
            $table->integer('duracion_dias');
            $table->timestamps();
        });
        Schema::create('limites_planes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_id');
            $table->string('tipo_limite');
            $table->integer('valor_limite');
            $table->timestamps();

            $table->foreign('plan_id')->references('id')->on('planes');
        });
        Schema::create('suscripciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id');
            $table->unsignedBigInteger('plan_id');
            $table->enum('estado', ['activa', 'inactiva', 'pendiente']);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->timestamps();

            $table->foreign('club_id')->references('id')->on('clubes');
            $table->foreign('plan_id')->references('id')->on('planes');
        });
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
        Schema::create('inventarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id');
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->integer('cantidad');
            $table->float('precio');
            $table->string('marca')->nullable();
            $table->timestamps();

            $table->foreign('club_id')->references('id')->on('clubes');
        });
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id');
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
            $table->timestamps();

            $table->foreign('club_id')->references('id')->on('clubes');
        });
        Schema::create('membresias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id');
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->float('precio');
            $table->integer('duracion_dias');
            $table->timestamps();

            $table->foreign('club_id')->references('id')->on('clubes');
        });
        Schema::create('pagos_club', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('membresia_id');
            $table->unsignedBigInteger('rol_usuario_id');
            $table->float('monto');
            $table->date('fecha_pago');
            $table->enum('estado', ['pendiente', 'completado', 'fallido']);
            $table->string('referencia_pago')->nullable();
            $table->timestamps();

            $table->foreign('membresia_id')->references('id')->on('membresias');
            $table->foreign('rol_usuario_id')->references('id')->on('roles_usuarios');
        });
        Schema::create('deportes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->string('referencia')->unique();
            $table->timestamps();
        });
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id');
            $table->unsignedBigInteger('deporte_id');
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->string('referencia')->unique();
            $table->timestamps();

            $table->foreign('club_id')->references('id')->on('clubes');
            $table->foreign('deporte_id')->references('id')->on('deportes');
        });
        Schema::create('clases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id');
            $table->unsignedBigInteger('profesor_id');
            $table->unsignedBigInteger('deporte_id');
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->timestamps();

            $table->foreign('club_id')->references('id')->on('clubes');
            $table->foreign('profesor_id')->references('id')->on('roles_usuarios');
            $table->foreign('deporte_id')->references('id')->on('deportes');
        });
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clase_id');
            $table->unsignedBigInteger('alumno_id');
            $table->enum('estado', ['presente', 'ausente', 'justificado']);
            $table->timestamps();

            $table->foreign('clase_id')->references('id')->on('clases');
            $table->foreign('alumno_id')->references('id')->on('roles_usuarios');
        });
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->string('mensaje');
            $table->boolean('leida')->default(false);
            $table->timestamps();

            $table->foreign('usuario_id')->references('id')->on('usuarios');
        });
        Schema::create('profesor_cache', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rol_usuario_id');
            $table->float('calificacion')->default(0);
            $table->integer('numero_calificaciones')->default(0);
            $table->text('filosofia')->nullable();
            $table->text('especializaciones')->nullable();
            $table->timestamp('last_updated');

            $table->foreign('rol_usuario_id')->references('id')->on('roles_usuarios');
        });
        Schema::create('alumno_cache', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rol_usuario_id');
            $table->unsignedBigInteger('padre_rol_usuario_id')->nullable();
            $table->timestamp('last_updated');

            $table->foreign('rol_usuario_id')->references('id')->on('roles_usuarios');
            $table->foreign('padre_rol_usuario_id')->references('id')->on('roles_usuarios');
        });
        Schema::create('padre_cache', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rol_usuario_id');
            $table->timestamp('last_updated');

            $table->foreign('rol_usuario_id')->references('id')->on('roles_usuarios');
        });
    }

    public function down()
    {
        return null;
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('clubes');
        Schema::dropIfExists('logs');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('roles_personalizados');
        Schema::dropIfExists('roles_usuarios');
        Schema::dropIfExists('permisos');
        Schema::dropIfExists('permisos_personalizados');
        Schema::dropIfExists('rol_permiso');
        Schema::dropIfExists('rol_personalizado_permiso');
        Schema::dropIfExists('planes');
        Schema::dropIfExists('limites_planes');
        Schema::dropIfExists('suscripciones');
        Schema::dropIfExists('pagos_plataforma');
        Schema::dropIfExists('inventarios');
        Schema::dropIfExists('eventos');
        Schema::dropIfExists('membresias');
        Schema::dropIfExists('pagos_club');
        Schema::dropIfExists('deportes');
        Schema::dropIfExists('categorias');
        Schema::dropIfExists('clases');
        Schema::dropIfExists('asistencias');
        Schema::dropIfExists('notificaciones');
        Schema::dropIfExists('profesor_cache');
        Schema::dropIfExists('alumno_cache');
        Schema::dropIfExists('padre_cache');
    }
};
