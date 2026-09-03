<?php

namespace Tests\Feature\EducationManager\Materi;

use App\Models\karyawan;
use App\Models\Materi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MateriEducationManagerTest extends TestCase
{
    use RefreshDatabase;

    protected User $educationManager;
    protected User $instruktur;

    protected function setUp(): void
    {
        parent::setUp();

        // Prevent SessionTimeout middleware from logging out in tests
        session(['last_activity' => time()]);

        // Create Spatie permissions required by MateriController
        Permission::firstOrCreate(['name' => 'View Materi']);
        Permission::firstOrCreate(['name' => 'Create Materi']);
        Permission::firstOrCreate(['name' => 'Edit Materi']);
        Permission::firstOrCreate(['name' => 'Delete Materi']);

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
        $this->educationManager->givePermissionTo(['View Materi', 'Create Materi', 'Edit Materi', 'Delete Materi']);

        // Setup Instruktur User & Karyawan
        $karyawanInstruktur = karyawan::create([
            'nama_lengkap' => 'Instruktur Test',
            'Divisi' => 'Education',
            'jabatan' => 'Instruktur',
            'status_aktif' => '1',
            'kode_karyawan' => 'INS_01',
            'nip' => 'NIP_INS_01',
        ]);

        $this->instruktur = User::create([
            'username' => 'instruktur_test',
            'jabatan' => 'Instruktur',
            'status_akun' => '1',
            'password' => bcrypt('password'),
            'karyawan_id' => $karyawanInstruktur->id,
        ]);
        $this->instruktur->givePermissionTo(['View Materi', 'Create Materi', 'Edit Materi', 'Delete Materi']);
    }

    public function test_education_manager_can_view_materi_index(): void
    {
        session(['last_activity' => time()]);

        Materi::create([
            'nama_materi' => 'Laravel Masterclass',
            'kode_materi' => 'MAT-001',
            'status' => 'Aktif',
        ]);

        $response = $this->actingAs($this->educationManager)->get(route('materi.index'));

        $response->assertStatus(200);
        $response->assertSee('Data Materi');
    }

    public function test_education_manager_can_view_create_materi_form(): void
    {
        session(['last_activity' => time()]);

        $response = $this->actingAs($this->educationManager)->get(route('materi.create'));

        $response->assertStatus(200);
    }

    public function test_education_manager_stores_materi_with_aktif_status(): void
    {
        session(['last_activity' => time()]);

        $payload = [
            'nama_materi' => 'Advanced Python for Data Science',
            'kode_materi' => 'PY-501',
            'kategori_materi' => 'Data Science',
            'vendor' => 'Inixindo',
            'durasi' => '4 Hari',
        ];

        $response = $this->actingAs($this->educationManager)->post(route('materi.store'), $payload);

        $response->assertRedirect(route('materi.index'));
        $this->assertDatabaseHas('materis', [
            'nama_materi' => 'Advanced Python for Data Science',
            'kode_materi' => 'PY-501',
            'status' => 'Aktif',
        ]);
    }

    public function test_non_education_manager_stores_materi_with_nonaktif_status(): void
    {
        session(['last_activity' => time()]);

        $payload = [
            'nama_materi' => 'Basic Cybersecurity',
            'kode_materi' => 'SEC-101',
            'kategori_materi' => 'Security',
            'vendor' => 'Inixindo',
            'durasi' => '3 Hari',
        ];

        $response = $this->actingAs($this->instruktur)->post(route('materi.store'), $payload);

        $response->assertRedirect(route('materi.index'));
        $this->assertDatabaseHas('materis', [
            'nama_materi' => 'Basic Cybersecurity',
            'kode_materi' => 'SEC-101',
            'status' => 'Nonaktif',
        ]);
    }

    public function test_education_manager_can_store_materi_with_silabus_pdf(): void
    {
        session(['last_activity' => time()]);
        Storage::fake('public');

        $file = UploadedFile::fake()->create('silabus_test.pdf', 500, 'application/pdf');

        $payload = [
            'nama_materi' => 'Docker and Kubernetes',
            'kode_materi' => 'DEVOPS-201',
            'kategori_materi' => 'DevOps',
            'vendor' => 'Inixindo',
            'durasi' => '5 Hari',
            'silabus' => $file,
        ];

        $response = $this->actingAs($this->educationManager)->post(route('materi.store'), $payload);

        $response->assertRedirect(route('materi.index'));

        $materi = Materi::where('kode_materi', 'DEVOPS-201')->first();
        $this->assertNotNull($materi);
        $this->assertNotNull($materi->silabus);
        Storage::disk('public')->assertExists($materi->silabus);
    }

    public function test_education_manager_can_view_materi_detail(): void
    {
        session(['last_activity' => time()]);

        $materi = Materi::create([
            'nama_materi' => 'Cloud Computing Fundamentals',
            'kode_materi' => 'CLOUD-101',
            'status' => 'Aktif',
        ]);

        $response = $this->actingAs($this->educationManager)->get(route('materi.show', $materi->id));

        $response->assertStatus(200);
        $response->assertSee('Detail Materi');
    }

    public function test_education_manager_can_view_edit_materi_form(): void
    {
        session(['last_activity' => time()]);

        $materi = Materi::create([
            'nama_materi' => 'Cloud Computing Fundamentals',
            'kode_materi' => 'CLOUD-101',
            'status' => 'Aktif',
        ]);

        $response = $this->actingAs($this->educationManager)->get(route('materi.edit', $materi->id));

        $response->assertStatus(200);
    }

    public function test_education_manager_can_update_materi_details(): void
    {
        session(['last_activity' => time()]);

        $materi = Materi::create([
            'nama_materi' => 'Old Title Materi',
            'kode_materi' => 'OLD-101',
            'status' => 'Aktif',
        ]);

        $payload = [
            'nama_materi' => 'Updated Title Materi',
            'kode_materi' => 'NEW-101',
            'kategori_materi' => 'Programming',
            'vendor' => 'Inixindo',
            'durasi' => '3 Hari',
        ];

        $response = $this->actingAs($this->educationManager)->put(route('materi.update', $materi->id), $payload);

        $response->assertRedirect(route('materi.index'));
        $this->assertDatabaseHas('materis', [
            'id' => $materi->id,
            'nama_materi' => 'Updated Title Materi',
            'kode_materi' => 'NEW-101',
        ]);
    }

    public function test_education_manager_can_update_materi_status_and_keterangan(): void
    {
        session(['last_activity' => time()]);

        $materi = Materi::create([
            'nama_materi' => 'Materi Status Test',
            'kode_materi' => 'STAT-101',
            'status' => 'Aktif',
        ]);

        $payload = [
            'status' => 'Nonaktif',
            'keterangan' => 'Kurikulum perlu diupdate',
            'tipe_materi' => 'Regular',
        ];

        $response = $this->actingAs($this->educationManager)->put(route('materi.update', $materi->id), $payload);

        $response->assertRedirect(route('materi.index'));
        $this->assertDatabaseHas('materis', [
            'id' => $materi->id,
            'status' => 'Nonaktif',
            'keterangan' => 'Kurikulum perlu diupdate',
        ]);
    }

    public function test_education_manager_can_delete_materi(): void
    {
        session(['last_activity' => time()]);

        $materi = Materi::create([
            'nama_materi' => 'Materi To Be Deleted',
            'kode_materi' => 'DEL-999',
            'status' => 'Aktif',
        ]);

        $response = $this->actingAs($this->educationManager)->delete(route('materi.destroy', $materi->id));

        $response->assertRedirect(route('materi.index'));
        $this->assertDatabaseMissing('materis', [
            'id' => $materi->id,
        ]);
    }

    public function test_education_manager_can_get_materi_by_id_json(): void
    {
        session(['last_activity' => time()]);

        $materi = Materi::create([
            'nama_materi' => 'JSON API Test Materi',
            'kode_materi' => 'JSON-100',
            'status' => 'Aktif',
        ]);

        $response = $this->actingAs($this->educationManager)->get(route('getMateriById', $materi->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => $materi->id,
                'nama_materi' => 'JSON API Test Materi',
            ],
        ]);
    }
}
