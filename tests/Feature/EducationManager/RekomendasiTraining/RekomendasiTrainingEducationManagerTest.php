<?php

namespace Tests\Feature\EducationManager\RekomendasiTraining;

use App\Models\User;
use App\Models\karyawan;
use App\Models\jabatan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RekomendasiTrainingEducationManagerTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $karyawan;

    protected function setUp(): void
    {
        parent::setUp();

        session(['last_activity' => time()]);

        $jabatan = jabatan::firstOrCreate(
            ['nama_jabatan' => 'Education Manager'],
            ['divisi' => 'Education']
        );

        $this->karyawan = karyawan::create([
            'nama_lengkap' => 'EM User Rekomendasi Test',
            'kode_karyawan' => 'EMREK001',
            'jabatan' => 'Education Manager',
            'divisi' => 'Education',
            'status_aktif' => '1',
        ]);

        $this->user = User::create([
            'id' => $this->karyawan->id,
            'username' => 'emrektest',
            'name' => 'EM User Rekomendasi Test',
            'jabatan' => 'Education Manager',
            'status_akun' => '1',
            'email' => 'emrektest@example.com',
            'password' => bcrypt('password'),
            'karyawan_id' => $this->karyawan->id,
        ]);
    }

    public function test_education_manager_can_view_rekomendasi_training_index_page(): void
    {
        session(['last_activity' => time()]);

        $response = $this->actingAs($this->user)
            ->get(route('rekomendasiLanjutan.index'));

        $response->assertStatus(200);
    }

    public function test_education_manager_can_fetch_rekomendasi_show_month_data(): void
    {
        session(['last_activity' => time()]);

        $year = date('Y');
        $month = date('m');

        $response = $this->actingAs($this->user)
            ->get("/rekomendasi-lanjutan/get/{$year}/{$month}");

        $response->assertStatus(200);
    }
}
