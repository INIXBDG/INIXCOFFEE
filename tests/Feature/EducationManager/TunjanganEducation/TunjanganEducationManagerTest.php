<?php

namespace Tests\Feature\EducationManager\TunjanganEducation;

use App\Models\karyawan;
use App\Models\rekapMengajarInstruktur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TunjanganEducationManagerTest extends TestCase
{
    use RefreshDatabase;

    protected User $educationManager;

    protected function setUp(): void
    {
        parent::setUp();

        session(['last_activity' => time()]);

        // Setup Education Manager User & Karyawan
        $karyawanEduman = karyawan::create([
            'nama_lengkap' => 'Education Manager Test',
            'Divisi' => 'Education',
            'jabatan' => 'Education Manager',
            'status_aktif' => '1',
            'kode_karyawan' => 'AD',
            'nip' => 'NIP_EDUMAN_01',
        ]);

        $this->educationManager = User::create([
            'username' => 'eduman_test',
            'jabatan' => 'Education Manager',
            'status_akun' => '1',
            'password' => bcrypt('password'),
            'karyawan_id' => $karyawanEduman->id,
        ]);
    }

    public function test_education_manager_can_view_tunjangan_education_index(): void
    {
        session(['last_activity' => time()]);

        $response = $this->actingAs($this->educationManager)->get(route('tunjanganEducation.index'));

        $response->assertStatus(200);
    }

    public function test_education_manager_can_fetch_rekap_instruktur_list_by_month_and_year(): void
    {
        session(['last_activity' => time()]);

        rekapMengajarInstruktur::create([
            'id_rkm' => '1',
            'id_instruktur' => 'AD',
            'feedback' => 95,
            'pax' => 10,
            'level' => 'Advanced',
            'durasi' => 4,
            'tanggal_awal' => now()->format('Y-m-d'),
            'tanggal_akhir' => now()->addDays(3)->format('Y-m-d'),
            'bulan' => (string) now()->month,
            'tahun' => (string) now()->year,
            'poin_durasi' => 40,
            'poin_pax' => 10,
            'tunjangan_feedback' => 500000,
            'total_tunjangan' => 1000000,
            'status' => 'Disetujui',
        ]);

        $response = $this->actingAs($this->educationManager)->get(route('getListRekapInstruktur', [
            'bulan' => now()->month,
            'tahun' => now()->year,
        ]));

        $response->assertStatus(200);
        $this->assertIsArray($response->json());
    }

    public function test_education_manager_can_export_tunjangan_education_excel(): void
    {
        session(['last_activity' => time()]);

        rekapMengajarInstruktur::create([
            'id_rkm' => '1',
            'id_instruktur' => 'AD',
            'feedback' => 90,
            'pax' => 5,
            'level' => 'Fundamental',
            'durasi' => 3,
            'tanggal_awal' => now()->format('Y-m-d'),
            'tanggal_akhir' => now()->addDays(2)->format('Y-m-d'),
            'bulan' => (string) now()->month,
            'tahun' => (string) now()->year,
            'total_tunjangan' => 500000,
        ]);

        $response = $this->actingAs($this->educationManager)->get(route('tunjanganEduExportExcel', [
            'month' => now()->month,
            'year' => now()->year,
        ]));

        $response->assertStatus(200);
    }
}
