<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles con nombres específicos
        $roles = [
            ['nombre' => 'admin', 'descripcion' => 'Acceso total a todas las funcionalidades.'],
            ['nombre' => 'profesor', 'descripcion' => 'Acceso a funciones básicas del sistema.'],
            ['nombre' => 'alumno', 'descripcion' => 'Gestiona contenido y usuarios dentro del sistema.'],
            ['nombre' => 'padre', 'descripcion' => 'Gestiona contenido y usuarios dentro del sistema.'],
        ];

        // Crear roles en la base de datos
        foreach ($roles as $rol) {
            Role::create($rol);
        }

        // Crear permisos para cada módulo
        $permisos = [
            // Permisos del dashboard
            ['nombre' => 'ver_dashboard', 'descripcion' => 'Permite ver el dashboard', 'referencia' => 'dashboard.view', 'modulo' => 'dashboard', 'acciones' => 'ver'],

            // Permisos del club
            ['nombre' => 'crear_club', 'descripcion' => 'Permite crear un club', 'referencia' => 'club.create', 'modulo' => 'club', 'acciones' => 'crear'],
            ['nombre' => 'editar_club', 'descripcion' => 'Permite editar un club', 'referencia' => 'club.edit', 'modulo' => 'club', 'acciones' => 'editar'],
            ['nombre' => 'eliminar_club', 'descripcion' => 'Permite eliminar un club', 'referencia' => 'club.delete', 'modulo' => 'club', 'acciones' => 'eliminar'],
            ['nombre' => 'ver_club', 'descripcion' => 'Permite ver el club', 'referencia' => 'club.view', 'modulo' => 'club', 'acciones' => 'ver'],

            // Permisos del entrenador
            ['nombre' => 'crear_entrenador', 'descripcion' => 'Permite crear un entrenador', 'referencia' => 'entrenador.create', 'modulo' => 'entrenador', 'acciones' => 'crear'],
            ['nombre' => 'editar_entrenador', 'descripcion' => 'Permite editar un entrenador', 'referencia' => 'entrenador.edit', 'modulo' => 'entrenador', 'acciones' => 'editar'],
            ['nombre' => 'eliminar_entrenador', 'descripcion' => 'Permite eliminar un entrenador', 'referencia' => 'entrenador.delete', 'modulo' => 'entrenador', 'acciones' => 'eliminar'],
            ['nombre' => 'ver_entrenador', 'descripcion' => 'Permite ver un entrenador', 'referencia' => 'entrenador.view', 'modulo' => 'entrenador', 'acciones' => 'ver'],

            // Permisos de los alumnos
            ['nombre' => 'crear_alumno', 'descripcion' => 'Permite crear un alumno', 'referencia' => 'alumno.create', 'modulo' => 'alumno', 'acciones' => 'crear'],
            ['nombre' => 'editar_alumno', 'descripcion' => 'Permite editar un alumno', 'referencia' => 'alumno.edit', 'modulo' => 'alumno', 'acciones' => 'editar'],
            ['nombre' => 'eliminar_alumno', 'descripcion' => 'Permite eliminar un alumno', 'referencia' => 'alumno.delete', 'modulo' => 'alumno', 'acciones' => 'eliminar'],
            ['nombre' => 'ver_alumno', 'descripcion' => 'Permite ver un alumno', 'referencia' => 'alumno.view', 'modulo' => 'alumno', 'acciones' => 'ver'],

            // Permisos del inventario
            ['nombre' => 'crear_inventario', 'descripcion' => 'Permite agregar elementos al inventario', 'referencia' => 'inventario.create', 'modulo' => 'inventario', 'acciones' => 'crear'],
            ['nombre' => 'editar_inventario', 'descripcion' => 'Permite editar el inventario', 'referencia' => 'inventario.edit', 'modulo' => 'inventario', 'acciones' => 'editar'],
            ['nombre' => 'eliminar_inventario', 'descripcion' => 'Permite eliminar elementos del inventario', 'referencia' => 'inventario.delete', 'modulo' => 'inventario', 'acciones' => 'eliminar'],
            ['nombre' => 'ver_inventario', 'descripcion' => 'Permite ver el inventario', 'referencia' => 'inventario.view', 'modulo' => 'inventario', 'acciones' => 'ver'],

            // configuraciones
            ['nombre' => 'ver_configuracion', 'descripcion' => 'Permite ver el configuracion', 'referencia' => 'configuracion.view', 'modulo' => 'configuracion', 'acciones' => 'ver'],

            // Permisos para padres
            ['nombre' => 'ver_padres', 'descripcion' => 'Permite ver la información de los padres', 'referencia' => 'padres.view', 'modulo' => 'padres', 'acciones' => 'ver'],
        ];

        // Crear permisos en la base de datos
        foreach ($permisos as $permiso) {
            Permission::create($permiso);
        }

        // Asignación de permisos a roles
        $rolesPermisos = [
            'admin' => ['ver_dashboard', 'crear_club', 'editar_club', 'eliminar_club', 'ver_club', 'crear_entrenador', 'editar_entrenador', 'eliminar_entrenador', 'ver_entrenador', 'crear_alumno', 'editar_alumno', 'eliminar_alumno', 'ver_alumno', 'crear_inventario', 'editar_inventario', 'eliminar_inventario', 'ver_inventario', 'ver_padres'],
            'profesor' => ['ver_dashboard', 'ver_club', 'ver_entrenador', 'ver_alumno', 'crear_alumno', 'editar_alumno', 'ver_inventario', 'ver_padres'],
            'alumno' => ['ver_dashboard', 'ver_club', 'ver_entrenador', 'ver_alumno'],
            'padre' => ['ver_dashboard', 'ver_club', 'ver_entrenador', 'ver_alumno', 'ver_padres'],
        ];


        // Asignar permisos a cada rol en la tabla rol_permiso
        foreach ($rolesPermisos as $rolNombre => $permisosAsignados) {
            $rol = Role::where('nombre', $rolNombre)->first();
            foreach ($permisosAsignados as $permisoNombre) {
                $permiso = Permission::where('nombre', $permisoNombre)->first();
                DB::table('rol_permiso')->insert([
                    'rol_id' => $rol->id,
                    'permiso_id' => $permiso->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
