<?php

namespace Tests\Feature\EducationManager\Peserta;

use App\Models\User;
use App\Models\karyawan;
use App\Models\jabatan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PesertaEducationManagerTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $karyawan;

    protected function setUp(): void
    {
        parent::setUp();

        session(['last_activity' => time()]);

        $role = Role::firstOrCreate(['name' => 'Education Manager']);
        $permission = Permission::firstOrCreate(['name' => 'View Perusahaan']);
        $role->givePermissionTo($permission);

        $jabatan = jabatan::firstOrCreate(
            ['nama_jabatan' => 'Education Manager'],
            ['divisi' => 'Education']
        );

        $this->karyawan = karyawan::create([
            'nama_lengkap' => 'EM User Peserta Test',
            'kode_karyawan' => 'EMPES001',
            'jabatan' => 'Education Manager',
            'divisi' => 'Education',
            'status_aktif' => '1',
        ]);

        $this->user = User::create([
            'id' => $this->karyawan->id,
            'username' => 'empestest',
            'name' => 'EM User Peserta Test',
            'jabatan' => 'Education Manager',
            'status_akun' => '1',
            'email' => 'empestest@example.com',
            'password' => bcrypt('password'),
            'karyawan_id' => $this->karyawan->id,
        ]);

        $this->user->assignRole($role);
    }

    public function test_education_manager_can_view_perusahaan_index_page(): void
    {
        session(['last_activity' => time()]);

        $response = $this->actingAs($this->user)
            ->get(route('perusahaan.index'));

        $response->assertStatus(200);
    }

    public function test_education_manager_can_view_registexam_index_page(): void
    {
        session(['last_activity' => time()]);

        $response = $this->actingAs($this->user)
            ->get(route('registexam.index'));

        $response->assertStatus(200);
    }

    public function test_education_manager_can_fetch_registexam_data(): void
    {
        session(['last_activity' => time()]);

        $response = $this->actingAs($this->user)
            ->get(route('getRegistrasiexam'));

        $response->assertStatus(200);
    }
}
