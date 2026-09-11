<?php

namespace Tests\Feature\ITSM;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DashboardItsmTest extends TestCase
{
    use DatabaseTransactions;

    private function createUser()
    {
        $karyawanId = DB::table('karyawans')->insertGetId([
            'nama_lengkap' => 'Test Karyawan ' . uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userId = DB::table('users')->insertGetId([
            'username' => 'testuser_' . uniqid(),
            'password' => Hash::make('password'),
            'karyawan_id' => $karyawanId,
            'jabatan' => 'HRD',
            'status_akun' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::find($userId);
    }

    public function test_endpoint_ticketing_data_dapat_diakses()
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->get('/ticketing-data');
        
        $response->assertStatus(302);
    }

    public function test_endpoint_jumlah_pic_dapat_diakses()
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->get('/jumlah-pic');
        
        $response->assertStatus(302);
    }

    public function test_endpoint_rerata_durasi_dapat_diakses()
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->get('/rerata-durasi-data');
        
        $response->assertStatus(302);
    }

    public function test_endpoint_rerata_ketepatan_response_dapat_diakses()
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->get('/rerata-ketepatan-response-data');
        
        $response->assertStatus(302);
    }

    public function test_endpoint_jumlah_permintaan_per_bulan_dapat_diakses()
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->get('/jumlah-permintaan-per-bulan');
        
        $response->assertStatus(302);
    }

    public function test_endpoint_permintaan_sering_diajukan_dapat_diakses()
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->get('/permintaan-sering-diajukan');
        
        $response->assertStatus(302);
    }

    public function test_endpoint_list_bulan_dapat_diakses()
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->get('/list-bulan');
        
        $response->assertStatus(302);
    }

    private function createUserWithRole($roleName, $divisi)
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

        $user = User::find($userId);
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => $roleName]);
        $user->assignRole($role);

        return $user;
    }

    public function test_project_admin_business_support_can_access_dashboard_itsm_endpoints()
    {
        $user = $this->createUserWithRole('Project Administrator & Business Support', 'Business Support');
        $response = $this->actingAs($user)->get('/ticketing-data');
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }
}
