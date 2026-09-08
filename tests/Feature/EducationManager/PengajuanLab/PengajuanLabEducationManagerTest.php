<?php

namespace Tests\Feature\EducationManager\PengajuanLab;

use App\Models\Lab;
use App\Models\PengajuanLabSubs;
use App\Models\User;
use App\Models\karyawan;
use App\Models\jabatan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PengajuanLabEducationManagerTest extends TestCase
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
            'nama_lengkap' => 'EM User Lab Test',
            'kode_karyawan' => 'EMLAB001',
            'jabatan' => 'Education Manager',
            'divisi' => 'Education',
            'status_aktif' => '1',
        ]);

        $this->user = User::create([
            'id' => $this->karyawan->id,
            'username' => 'emlabtest',
            'name' => 'EM User Lab Test',
            'jabatan' => 'Education Manager',
            'status_akun' => '1',
            'email' => 'emlabtest@example.com',
            'password' => bcrypt('password'),
            'karyawan_id' => $this->karyawan->id,
        ]);
    }

    public function test_education_manager_can_view_pengajuan_lab_index_page(): void
    {
        session(['last_activity' => time()]);

        $response = $this->actingAs($this->user)
            ->get(route('pengajuanlabsdansubs.index'));

        $response->assertStatus(200);
    }

    public function test_education_manager_can_fetch_pengajuan_lab_subs_data(): void
    {
        session(['last_activity' => time()]);

        $month = date('m');
        $year = date('Y');

        $response = $this->actingAs($this->user)
            ->get(route('getPengajuanLabSubs', ['month' => $month, 'year' => $year]));

        $response->assertStatus(200);
    }

    public function test_education_manager_can_update_lab_subs_details(): void
    {
        session(['last_activity' => time()]);

        $lab = Lab::create([
            'kode_karyawan' => $this->karyawan->kode_karyawan,
            'nama_labs' => 'Practice Lab Cisco',
            'merk' => 'Cisco',
            'tipe' => 'one-time',
            'desc' => 'Practice Lab for Cisco Networking',
            'mata_uang' => 'Rupiah',
            'harga' => 1000000,
            'harga_rupiah' => 1000000,
            'status' => 'active',
        ]);

        $pengajuanLab = PengajuanLabSubs::create([
            'kode_karyawan' => $this->karyawan->kode_karyawan,
            'id_labs' => $lab->id,
        ]);

        $payload = [
            'nama_labs' => 'Practice Lab Cisco Updated',
            'merk' => 'Cisco Systems',
            'tipe' => 'one-time',
            'mata_uang' => 'Rupiah',
            'harga' => 1200000,
            'harga_rupiah' => '1.200.000',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->user)
            ->put(route('pengajuanlabsdansubs.updatelabsubs', $pengajuanLab->id), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('labs', [
            'id' => $lab->id,
            'nama_labs' => 'Practice Lab Cisco Updated',
        ]);
    }
}
