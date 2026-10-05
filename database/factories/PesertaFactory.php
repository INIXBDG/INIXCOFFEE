<?php

namespace Database\Factories;

use App\Models\Peserta;
use Illuminate\Database\Eloquent\Factories\Factory;

class PesertaFactory extends Factory
{
    protected $model = Peserta::class;

    public function definition(): array
    {
        return [
            'nama'           => fake()->name(),
            'jenis_kelamin'  => fake()->randomElement(['Laki-laki', 'Perempuan']),
            'email'          => fake()->unique()->safeEmail(),
            'no_hp'          => fake()->numerify('08##########'),
            'alamat'         => fake()->address(),
            'perusahaan_key' => fake()->bothify('PRS-###'),
            'tanggal_lahir'  => fake()->date('Y-m-d'),
        ];
    }
}