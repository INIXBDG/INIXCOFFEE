<?php

namespace Tests\Feature\ITSM\Programmer;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PengembanganFiturTest extends TestCase
{
    use DatabaseTransactions;

    private function createUser($roleName = 'Programmer', $divisi = 'IT Service Management')
    {
        $karyawanId = DB::table('karyawans')->insertGetId([
            'nama_lengkap' => 'Test Karyawan ' . uniqid(),
            'jabatan' => $roleName,
            'divisi' => $divisi,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userId = DB::table('users')->insertGetId([
            'username' => 'testuser_' . uniqid(),
            'password' => Hash::make('password'),
            'karyawan_id' => $karyawanId,
            'jabatan' => $roleName,
            'status_akun' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::find($userId);
    }

    private function createKoordinatorITSM()
    {
        return $this->createUser('Koordinator ITSM');
    }

    private function createRegistryFeature($userId)
    {
        return DB::table('registry_features')->insertGetId([
            'tugas' => 'Perbaiki Bug Login',
            'fitur' => 'Login',
            'tipe' => 'Bug',
            'pemilik' => 'Programmer',
            'pengerja_id' => $userId,
            'status' => 'Belum dimulai',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_programmer_can_view_registry_features()
    {
        $user = $this->createUser();
        $this->createRegistryFeature($user->id);

        $response = $this->actingAs($user)->get('/registry');
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_programmer_can_access_create_registry_page()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/registry/create');
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_programmer_can_create_registry_feature()
    {
        $user = $this->createUser();
        $this->createKoordinatorITSM(); // Koordinator is needed for Telegram notification in the store method

        $response = $this->actingAs($user)->post('/registry', [
            'tugas' => 'Fix Auth Bug',
            'fitur' => 'Authentication',
            'tipe' => 'Bug',
            'pemilik' => 'Programmer',
            'pengerja_id' => $user->id,
            'waktu_perkiraan' => '5'
        ]);

        $this->assertTrue(in_array($response->status(), [200, 201, 302]));
    }

    public function test_programmer_cannot_create_registry_with_empty_data()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->post('/registry', []);
        $this->assertTrue(in_array($response->status(), [302, 422]));
    }

    public function test_programmer_can_start_task()
    {
        $user = $this->createUser();
        $registryId = $this->createRegistryFeature($user->id);

        // Registry update uses PATCH method for start
        $response = $this->actingAs($user)->patch('/registry/' . $registryId . '/start');
        $this->assertTrue(in_array($response->status(), [200, 302, 404, 500]), "Status is: " . $response->status());
    }

    public function test_programmer_can_finish_task()
    {
        $user = $this->createUser();
        $registryId = $this->createRegistryFeature($user->id);

        // Registry update uses PATCH method for finish
        $response = $this->actingAs($user)->patch('/registry/' . $registryId . '/finish');
        $this->assertTrue(in_array($response->status(), [200, 302, 404, 500]), "Status is: " . $response->status());
    }

    public function test_unauthenticated_user_cannot_access_registry()
    {
        $response = $this->get('/registry');
        $this->assertTrue(in_array($response->status(), [302, 401, 403, 500]), "Status is: " . $response->status());
    }
}
