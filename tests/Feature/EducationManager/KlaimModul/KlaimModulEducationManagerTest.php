<?php

namespace Tests\Feature\EducationManager\KlaimModul;

use App\Models\karyawan;
use App\Models\Module;
use App\Models\PengajuanKlaimModul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KlaimModulEducationManagerTest extends TestCase
{
    use RefreshDatabase;

    protected User $educationManager;
    protected karyawan $karyawanEduman;

    protected function setUp(): void
    {
        parent::setUp();

        session(['last_activity' => time()]);

        // Setup Education Manager User & Karyawan
        $this->karyawanEduman = karyawan::create([
            'nama_lengkap' => 'Education Manager Klaim Test',
            'divisi' => 'Education',
            'jabatan' => 'Education Manager',
            'status_aktif' => '1',
            'kode_karyawan' => 'AD',
            'nip' => 'NIP_EDUMAN_KLAIM',
        ]);

        $this->educationManager = User::create([
            'id' => $this->karyawanEduman->id,
            'username' => 'eduman_klaim_user',
            'jabatan' => 'Education Manager',
            'status_akun' => '1',
            'password' => bcrypt('password'),
            'karyawan_id' => $this->karyawanEduman->id,
            'id_instruktur' => $this->karyawanEduman->id,
        ]);
    }

    public function test_education_manager_can_view_klaim_modul_index_page(): void
    {
        session(['last_activity' => time()]);

        $response = $this->actingAs($this->educationManager)->get(route('pengajuanklaimmodul.index'));

        $response->assertStatus(200);
    }

    public function test_education_manager_can_fetch_klaim_modul_data(): void
    {
        session(['last_activity' => time()]);

        $response = $this->actingAs($this->educationManager)->get(route('pengajuanklaimmodul.data', [
            'month' => now()->month,
            'year' => now()->year,
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_education_manager_can_approve_klaim_modul(): void
    {
        session(['last_activity' => time()]);

        $module = Module::create([
            'title' => 'Modul React & TypeScript Advanced',
            'category' => 'Web Development',
            'link' => 'https://example.com/modul',
            'description' => 'Deskripsi Modul',
            'kode_karyawan' => $this->karyawanEduman->kode_karyawan,
        ]);

        $klaimModul = PengajuanKlaimModul::create([
            'module_id' => $module->id,
            'status' => 'Pending',
        ]);

        $payload = [
            'approval' => '1',
            'price' => 750000,
        ];

        $response = $this->actingAs($this->educationManager)->put(route('pengajuanklaimmodul.approve', $klaimModul->id), $payload);

        $response->assertStatus(302);
        $this->assertDatabaseHas('pengajuan_klaim_modul', [
            'id' => $klaimModul->id,
            'status' => 'Disetujui oleh Education Manager',
        ]);
    }
}
