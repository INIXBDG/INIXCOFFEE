<?php

namespace Tests\Feature\EducationManager\PermissionsAndRoles;

use App\Models\karyawan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EducationManagerPermissionsAndRolesTest extends TestCase
{
    use DatabaseTransactions;

    protected User $educationManager;
    protected User $instruktur;

    protected function setUp(): void
    {
        parent::setUp();

        session(['last_activity' => time()]);

        // Create Spatie Permissions & Roles
        $edumanRole = Role::firstOrCreate(['name' => 'Education Manager']);
        $instrukturRole = Role::firstOrCreate(['name' => 'Instruktur']);

        $viewMateri = Permission::firstOrCreate(['name' => 'View Materi']);
        $createMateri = Permission::firstOrCreate(['name' => 'Create Materi']);
        $editMateri = Permission::firstOrCreate(['name' => 'Edit Materi']);
        $deleteMateri = Permission::firstOrCreate(['name' => 'Delete Materi']);

        $edumanRole->givePermissionTo([$viewMateri, $createMateri, $editMateri, $deleteMateri]);
        $instrukturRole->givePermissionTo([$viewMateri]);

        // Setup Karyawan Education Manager
        $karyawanEduman = karyawan::create([
            'nama_lengkap' => 'Education Manager Hak Akses',
            'divisi' => 'Education',
            'jabatan' => 'Education Manager',
            'status_aktif' => '1',
            'kode_karyawan' => 'AD',
            'nip' => 'NIP_EDUMAN_ROLES',
        ]);

        $this->educationManager = User::create([
            'id' => $karyawanEduman->id,
            'username' => 'eduman_role_user',
            'jabatan' => 'Education Manager',
            'status_akun' => '1',
            'password' => bcrypt('password'),
            'karyawan_id' => $karyawanEduman->id,
            'id_instruktur' => $karyawanEduman->id,
        ]);
        $this->educationManager->assignRole($edumanRole);

        // Setup Karyawan Instruktur
        $karyawanInstruktur = karyawan::create([
            'nama_lengkap' => 'Instruktur Standard',
            'divisi' => 'Education',
            'jabatan' => 'Instruktur',
            'status_aktif' => '1',
            'kode_karyawan' => 'INS_01',
            'nip' => 'NIP_INS_ROLES',
        ]);

        $this->instruktur = User::create([
            'id' => $karyawanInstruktur->id,
            'username' => 'instruktur_role_user',
            'jabatan' => 'Instruktur',
            'status_akun' => '1',
            'password' => bcrypt('password'),
            'karyawan_id' => $karyawanInstruktur->id,
            'id_instruktur' => $karyawanInstruktur->id,
        ]);
        $this->instruktur->assignRole($instrukturRole);
    }

    public function test_education_manager_has_correct_role_and_permissions(): void
    {
        $this->assertTrue($this->educationManager->hasRole('Education Manager'));
        $this->assertTrue($this->educationManager->hasPermissionTo('View Materi'));
        $this->assertTrue($this->educationManager->hasPermissionTo('Create Materi'));
        $this->assertTrue($this->educationManager->hasPermissionTo('Edit Materi'));
        $this->assertTrue($this->educationManager->hasPermissionTo('Delete Materi'));
    }

    public function test_instruktur_does_not_have_education_manager_edit_delete_permissions(): void
    {
        $this->assertTrue($this->instruktur->hasRole('Instruktur'));
        $this->assertTrue($this->instruktur->hasPermissionTo('View Materi'));
        $this->assertFalse($this->instruktur->hasPermissionTo('Create Materi'));
        $this->assertFalse($this->instruktur->hasPermissionTo('Edit Materi'));
        $this->assertFalse($this->instruktur->hasPermissionTo('Delete Materi'));
    }

    public function test_education_manager_jabatan_attribute_is_education_manager(): void
    {
        $this->assertEquals('Education Manager', $this->educationManager->jabatan);
        $this->assertEquals('Education Manager', $this->educationManager->karyawan->jabatan);
    }
}
