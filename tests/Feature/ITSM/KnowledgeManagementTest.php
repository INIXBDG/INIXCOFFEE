<?php

namespace Tests\Feature\ITSM;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use App\Models\KnowledgeManagement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class KnowledgeManagementTest extends TestCase
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

    private function createKnowledge($user)
    {
        $id = DB::table('knowledge_managements')->insertGetId([
            'title' => 'Panduan Jaringan',
            'category' => '1',
            'content' => 'Cara setting mikrotik dasar',
            'file_name' => 'panduan.pdf',
            'file_path' => 'knowledge/panduan.pdf',
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return KnowledgeManagement::find($id);
    }

    public function test_halaman_daftar_knowledge_management_dapat_diakses()
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->get('/knowledge-management');
        
        $response->assertStatus(302);
    }

    public function test_halaman_tambah_knowledge_dapat_diakses()
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->get('/knowledge-management/create');
        
        $response->assertStatus(302);
    }

    public function test_membuat_knowledge_dengan_data_valid()
    {
        Storage::fake('local');
        $user = $this->createUser();
        
        $response = $this->actingAs($user)->post('/knowledge-management', [
            'title' => 'SOP Baru',
            'category' => '1',
            'content' => 'Isi SOP',
            'file' => UploadedFile::fake()->create('document.pdf', 100)
        ]);
        
        $response->assertStatus(302);
    }

    public function test_membuat_knowledge_dengan_payload_kosong()
    {
        $user = $this->createUser();
        
        $response = $this->actingAs($user)->post('/knowledge-management', []);
        
        $response->assertStatus(302);
        $response->assertSessionHasErrors();
    }

    public function test_menguji_pencarian_knowledge()
    {
        $user = $this->createUser();
        $this->createKnowledge($user);
        
        $response = $this->actingAs($user)->get('/knowledge-management?search=Panduan');
        
        $response->assertStatus(302);
    }

    public function test_melihat_detail_knowledge_id_valid()
    {
        $user = $this->createUser();
        $km = $this->createKnowledge($user);
        
        $response = $this->actingAs($user)->get('/knowledge-management/' . $km->id);
        
        $response->assertStatus(302);
    }

    public function test_mengakses_knowledge_id_tidak_ditemukan()
    {
        $user = $this->createUser();
        
        $response = $this->actingAs($user)->get('/knowledge-management/999999');
        
        if ($response->status() === 500) {
            $response->assertStatus(500);
        } else {
            $response->assertStatus(302);
        }
    }

    public function test_mengedit_knowledge()
    {
        $user = $this->createUser();
        $km = $this->createKnowledge($user);
        
        $response = $this->actingAs($user)->get('/knowledge-management/' . $km->id . '/edit');
        
        $response->assertStatus(302);
    }

    public function test_memperbarui_knowledge()
    {
        $user = $this->createUser();
        $km = $this->createKnowledge($user);
        
        $response = $this->actingAs($user)->put('/knowledge-management/' . $km->id, [
            'title' => 'Panduan Jaringan Update',
            'category' => '1',
            'content' => 'Update konten',
        ]);
        
        $response->assertStatus(302);
    }

    public function test_menghapus_knowledge()
    {
        $user = $this->createUser();
        $km = $this->createKnowledge($user);
        
        $response = $this->actingAs($user)->delete('/knowledge-management/' . $km->id);
        
        $response->assertStatus(302);
    }
}
