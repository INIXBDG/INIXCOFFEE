<?php

namespace Database\Factories;

use App\Models\Karyawan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Karyawan>
 */
class KaryawanFactory extends Factory
{
protected $model = Karyawan::class;

    public function definition(): array
    {
        return [
            'nip'               => fake()->unique()->numerify('##########'),
            'nama_lengkap'      => fake()->name(),
            'alamat_lengkap'    => fake()->address(),
            'gender'            => fake()->randomElement(['Laki-laki', 'Perempuan']),
            'tempat_lahir'      => fake()->city(),
            'tanggal_lahir'     => fake()->dateTimeBetween('-45 years', '-22 years')->format('Y-m-d'),
            'religion'          => fake()->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha']),
            'provinsi'          => 'Jawa Barat',
            'kota'              => 'Bandung',
            'divisi'            => fake()->randomElement(['Office', 'Sales', 'Education', 'HRD', 'Finance']),
            'jabatan'           => fake()->jobTitle(),
            'status_aktif'      => '1',
            'kode_karyawan'     => fake()->unique()->bothify('KRY-####'),
            'email'             => fake()->unique()->safeEmail(),
            // 'Telepon'           => fake()->numerify('08##########'),
            'cuti'              => 12,
            'gaji'              => 5000000,
            'tunjangan_jabatan' => 500000,
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(fn () => ['status_aktif' => '0']);
    }

    public function resign(): static
    {
        return $this->state(fn () => [
            'status_aktif'  => '0',
            'resigned_at'   => now()->subMonth(),
            'alasan_resign' => 'Pindah kerja',
        ]);
    }

    public function probation(): static
    {
        return $this->state(fn () => [
            'awal_probation'  => now()->subMonth()->toDateString(),
            'akhir_probation' => now()->addMonths(2)->toDateString(),
        ]);
    }

    public function kontrak(): static
    {
        return $this->state(fn () => [
            'awal_kontrak'  => now()->subMonths(3)->toDateString(),
            'akhir_kontrak' => now()->addMonths(9)->toDateString(),
        ]);
    }

    public function tetap(): static
    {
        return $this->state(fn () => [
            'awal_tetap' => now()->subYear()->toDateString(),
        ]);
    }
}