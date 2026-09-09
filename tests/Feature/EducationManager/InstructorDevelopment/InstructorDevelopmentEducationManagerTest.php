<?php

namespace Tests\Feature\EducationManager\InstructorDevelopment;

use App\Models\karyawan;
use App\Models\Pelatihan;
use App\Models\Sertifikasi;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InstructorDevelopmentEducationManagerTest extends TestCase
{
    use DatabaseTransactions;

    protected User $educationManager;
    protected User $instruktur;

    protected function setUp(): void
    {
        parent::setUp();

        session(['last_activity' => time()]);

        // Setup Education Manager User & Karyawan
        $karyawanEduman = karyawan::create([
            'nama_lengkap' => 'Education Manager Dev Test',
            'divisi' => 'Education',
            'jabatan' => 'Education Manager',
            'status_aktif' => '1',
            'kode_karyawan' => 'AD',
            'nip' => 'NIP_EDUMAN_DEV',
        ]);

        $this->educationManager = User::create([
            'id' => $karyawanEduman->id,
            'username' => 'eduman_dev_user',
            'jabatan' => 'Education Manager',
            'status_akun' => '1',
            'password' => bcrypt('password'),
            'karyawan_id' => $karyawanEduman->id,
            'id_instruktur' => $karyawanEduman->id,
        ]);

        // Setup Instruktur User & Karyawan
        $karyawanInstruktur = karyawan::create([
            'nama_lengkap' => 'Instruktur Development Test',
            'divisi' => 'Education',
            'jabatan' => 'Instruktur',
            'status_aktif' => '1',
            'kode_karyawan' => 'INS_DEV_01',
            'nip' => 'NIP_INS_DEV',
        ]);

        $this->instruktur = User::create([
            'id' => $karyawanInstruktur->id,
            'username' => 'instruktur_dev_user',
            'jabatan' => 'Instruktur',
            'status_akun' => '1',
            'password' => bcrypt('password'),
            'karyawan_id' => $karyawanInstruktur->id,
            'id_instruktur' => $karyawanInstruktur->id,
        ]);
    }

    public function test_education_manager_can_access_instructor_development_dashboard(): void
    {
        session(['last_activity' => time()]);

        $response = $this->actingAs($this->educationManager)->get(route('development.index'));

        $response->assertStatus(200);
    }

    public function test_education_manager_can_approve_sertifikasi_submission(): void
    {
        session(['last_activity' => time()]);

        $sertifikasi = Sertifikasi::create([
            'user_id' => $this->instruktur->id,
            'nama_sertifikat' => 'AWS Certified Solutions Architect',
            'tanggal_ujian' => now()->format('Y-m-d'),
            'harga' => 1500000,
            'vendor' => 'Amazon Web Services',
            'status_approval' => 'pending',
        ]);

        $payload = [
            'status_approval' => 'approved',
        ];

        $response = $this->actingAs($this->educationManager)->post(route('sertifikasi.approve', $sertifikasi->id), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('sertifikasis', [
            'id' => $sertifikasi->id,
            'status_approval' => 'approved',
            'approved_by' => $this->educationManager->id,
        ]);
    }

    public function test_education_manager_can_approve_pelatihan_submission(): void
    {
        session(['last_activity' => time()]);

        $pelatihan = Pelatihan::create([
            'user_id' => $this->instruktur->id,
            'nama_pelatihan' => 'Deep Learning & Neural Networks',
            'tanggal_mulai' => now()->format('Y-m-d'),
            'tanggal_selesai' => now()->addDay()->format('Y-m-d'),
            'harga' => 3000000,
            'vendor' => 'Inixindo',
            'status_approval' => 'pending',
        ]);

        $payload = [
            'status_approval' => 'approved',
        ];

        $response = $this->actingAs($this->educationManager)->post(route('pelatihan.approve', $pelatihan->id), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('pelatihans', [
            'id' => $pelatihan->id,
            'status_approval' => 'approved',
            'approved_by' => $this->educationManager->id,
        ]);
    }

    public function test_non_education_manager_cannot_approve_sertifikasi_submission(): void
    {
        session(['last_activity' => time()]);

        $sertifikasi = Sertifikasi::create([
            'user_id' => $this->instruktur->id,
            'nama_sertifikat' => 'CKA Certified Kubernetes Administrator',
            'tanggal_ujian' => now()->format('Y-m-d'),
            'harga' => 2000000,
            'vendor' => 'CNCF',
            'status_approval' => 'pending',
        ]);

        $payload = [
            'status_approval' => 'approved',
        ];

        $response = $this->actingAs($this->instruktur)->post(route('sertifikasi.approve', $sertifikasi->id), $payload);

        $response->assertStatus(403);
    }
}
