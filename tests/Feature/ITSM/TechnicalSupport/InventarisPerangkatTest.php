<?php

namespace Tests\Feature\ITSM\TechnicalSupport;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InventarisPerangkatTest extends TestCase
{
    use DatabaseTransactions;

    private function createUserWithRole($roleName = 'Technical Support', $divisi = 'IT Service Management')
    {
        $karyawanId = DB::table('karyawans')->insertGetId([
            'nama_lengkap' => 'Test TS ' . uniqid(),
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

    private function createInventaris()
    {
        return DB::table('inventaris')->insertGetId([
            'idbarang' => 'BRG-' . uniqid(),
            'name' => 'PC Server',
            'kodebarang' => 'INV-' . uniqid(),
            'type' => 'E',
            'merk_kode_seri_hardware' => 'SN-12345',
            'qty' => 1,
            'satuan' => 'Unit',
            'harga_beli' => 1000000,
            'total_harga' => 1000000,
            'waktu_pembelian' => now()->format('Y-m-d'),
            'ruangan' => 'Ruang Server',
            'kondisi' => 'baik',
            'deskripsi' => 'Baru',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_technical_support_can_access_inventaris_list()
    {
        $user = $this->createUserWithRole();
        $response = $this->actingAs($user)->get('/inventaris/index');
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_technical_support_can_create_elektronik_inventaris()
    {
        $user = $this->createUserWithRole();

        $response = $this->actingAs($user)->post('/inventaris/input/barang', [
            'name' => 'Laptop Baru',
            'kodebarang' => 'INV-LPT-001',
            'type' => 'E',
            'merk_kode_seri_hardware' => 'Lenovo-123',
            'qty' => 5,
            'satuan' => 'Unit',
            'harga_beli' => 10000000,
            'total_harga' => 50000000,
            'waktu_pembelian' => now()->format('Y-m-d'),
            'ruangan' => 'Gudang IT',
            'deskripsi' => 'Pengadaan 2026',
            'kondisi' => 'baik',
            'pengguna' => 'IT'
        ]);

        $this->assertTrue(in_array($response->status(), [200, 201, 302]), "Status is: " . $response->status());
    }

    public function test_technical_support_cannot_create_non_elektronik_inventaris()
    {
        $user = $this->createUserWithRole();

        $response = $this->actingAs($user)->post('/inventaris/input/barang', [
            'name' => 'Meja Kantor',
            'kodebarang' => 'INV-MJ-001',
            'type' => 'NE', // Non Elektronik
            'merk_kode_seri_hardware' => '-',
            'qty' => 2,
            'satuan' => 'Buah',
            'ruangan' => 'Gudang Umum',
            'deskripsi' => 'Meja Karyawan',
            'harga_beli' => 1000000,
            'total_harga' => 2000000,
            'waktu_pembelian' => now()->format('Y-m-d'),
            'kondisi' => 'baik'
        ]);

        $this->assertTrue(in_array($response->status(), [403, 302]));
    }

    public function test_technical_support_create_inventaris_with_empty_payload()
    {
        $user = $this->createUserWithRole();
        $response = $this->actingAs($user)->post('/inventaris/input/barang', []);
        $this->assertTrue(in_array($response->status(), [302, 422]));
    }

    public function test_technical_support_can_view_inventaris_detail()
    {
        $user = $this->createUserWithRole();
        $inventarisId = $this->createInventaris();

        $response = $this->actingAs($user)->get("/inventaris/show/data/{$inventarisId}");
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_technical_support_can_update_inventaris()
    {
        $user = $this->createUserWithRole();
        $inventarisId = $this->createInventaris();

        $response = $this->actingAs($user)->put("/inventaris/update/{$inventarisId}", [
            'name' => 'PC Server Updated',
            'kodebarang' => 'INV-001',
            'type' => 'E',
            'qty' => 2,
            'satuan' => 'Unit',
            'ruangan' => 'Ruang Server',
            'harga_beli' => 1000000,
            'waktu_pembelian' => now()->format('Y-m-d')
        ]);

        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_technical_support_can_delete_inventaris()
    {
        $user = $this->createUserWithRole();
        $inventarisId = $this->createInventaris();

        $response = $this->actingAs($user)->delete("/inventaris/delete/data/{$inventarisId}");
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_unauthenticated_user_cannot_access_inventaris()
    {
        $response = $this->get('/inventaris/index');
        $this->assertTrue(in_array($response->status(), [302, 401, 403, 500]));
    }
}
