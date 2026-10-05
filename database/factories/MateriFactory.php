<?php

namespace Database\Factories;

use App\Models\Materi;
use Illuminate\Database\Eloquent\Factories\Factory;

class MateriFactory extends Factory
{
    protected $model = Materi::class;

    public function definition(): array
    {
        return [
            'nama_materi'     => fake()->sentence(2),
            'kode_materi'     => fake()->unique()->bothify('MAT-###'),
            'kategori_materi' => fake()->randomElement([
                'Programming',
                'Database',
                'Networking',
                'Microsoft',
            ]),
            'vendor'          => fake()->randomElement([
                'Microsoft',
                'Oracle',
                'Cisco',
                'Internal',
            ]),
            'durasi'          => '5 Hari',
            'status'          => fake()->randomElement([
                'Aktif',
                'Nonaktif',
                'Retired',
            ]),
            'keterangan'      => fake()->sentence(),
            'tipe_materi'     => fake()->randomElement([
                'Regular',
                'Exam',
                'Workshop',
            ]),
            'silabus'         => fake()->sentence(),
            'alias'           => fake()->sentence(2),
            'kode_alias'      => fake()->unique()->bothify('ALIAS-###'),
            'alias_exam'      => fake()->sentence(2),
            'kategori_exam'   => fake()->randomElement([
                'International',
                'BNSP',
            ]),
        ];
    }
}