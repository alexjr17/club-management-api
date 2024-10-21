<?php

namespace Database\Factories;

use App\Models\ClubDeportivo\Club;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserRole>
 */
class UserRoleFactory extends Factory
{
    protected $model = UserRole::class;

    public function definition()
    {
        $user = User::factory()->create();
        $roleId = ($user->id == 1) ? 1 : 2; // Asigna el rol 1 solo al usuario 1, de lo contrario, asigna el rol 2

        return [
            'usuario_id' => $user->id,
            'rol_id' => $roleId,
            'rol_personalizado_id' => null,
            'club_id' => Club::factory(),
        ];
        // return [
        //     'usuario_id' => User::factory(),
        //     'rol_id' => Role::factory(),
        //     'rol_personalizado_id' => null,
        //     'club_id' => Club::factory(),
        // ];
    }

    public function conRolPersonalizado()
    {
        return $this->state(function (array $attributes) {
            return [
                'rol_id' => null,
                // 'rol_personalizado_id' => RolPersonalizado::factory(),
            ];
        });
    }
}
