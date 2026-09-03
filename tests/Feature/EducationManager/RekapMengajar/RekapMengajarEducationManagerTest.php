<?php

namespace Tests\Feature\EducationManager\RekapMengajar;

use App\Models\User;
use App\Models\karyawan;
use App\Models\jabatan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RekapMengajarEducationManagerTest extends TestCase
{
    use RefreshDatabase;

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
            'nama_lengkap' => 'EM User Rekap Test',
            'kode_karyawan' => 'EMREKAP001',
            'jabatan' => 'Education Manager',
            'divisi' => 'Education',
            'status_aktif' => '1',
        ]);

        $this->user = User::create([
            'id' => $this->karyawan->id,
            'username' => 'emrekaptest',
            'name' => 'EM User Rekap Test',
            'jabatan' => 'Education Manager',
            'status_akun' => '1',
            'email' => 'emrekaptest@example.com',
            'password' => bcrypt('password'),
            'karyawan_id' => $this->karyawan->id,
        ]);
    }

    public function test_education_manager_can_view_rekap_mengajar_index_page(): void
    {
        session(['last_activity' => time()]);

        $response = $this->actingAs($this->user)
            ->get(route('rekapmengajarinstruktur.index'));

        $response->assertStatus(200);
    }

    public function test_education_manager_can_fetch_list_mengajar_data(): void
    {
        session(['last_activity' => time()]);

        $bulan = date('m');
        $tahun = date('Y');

        $response = $this->actingAs($this->user)
            ->get(route('getListMengajar', ['bulan' => $bulan, 'tahun' => $tahun]));

        $response->assertStatus(200);
    }
}
