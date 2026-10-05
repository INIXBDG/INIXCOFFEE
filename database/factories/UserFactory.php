<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'username'       => fake()->unique()->userName(),
            'jabatan'        => fake()->jobTitle(),
            'status_akun'    => '1',
            'password'       => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'karyawan_id'    => null,
            'id_instruktur'  => null,
            'id_sales'       => null,
            'remember_token' => Str::random(10),
            'role'           => null,
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(fn () => ['status_akun' => '0']);
    }

    public function denganKolomRole(string $role): static
    {
        return $this->state(fn () => ['role' => $role]);
    }
}