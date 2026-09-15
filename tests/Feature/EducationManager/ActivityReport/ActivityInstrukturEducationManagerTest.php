<?php

namespace Tests\Feature\EducationManager\ActivityReport;

use App\Models\ActivityInstruktur;
use App\Models\karyawan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ActivityInstrukturEducationManagerTest extends TestCase
{
    use DatabaseTransactions;

    protected User $educationManager;

    protected function setUp(): void
    {
        parent::setUp();

        session(['last_activity' => time()]);

        // Setup Education Manager User & Karyawan with matching IDs
        $karyawanEduman = karyawan::create([
            'nama_lengkap' => 'Education Manager Test',
            'divisi' => 'Education',
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
            'id_instruktur' => $karyawanEduman->id,
        ]);

        $this->educationManager->id = $karyawanEduman->id;
        $this->educationManager->save();
    }

    public function test_education_manager_can_view_activities_index_page(): void
    {
        session(['last_activity' => time()]);

        $response = $this->actingAs($this->educationManager)->get(route('activities.index'));

        $response->assertStatus(200);
    }

    public function test_education_manager_can_store_manual_activity(): void
    {
        session(['last_activity' => time()]);

        $payload = [
            'activity_date' => now()->format('Y-m-d'),
            'activity' => 'Pengembangan Kurikulum AI',
            'activity_type' => 'Sharing Knowledge',
            'desc' => 'Menyusun silabus baru',
        ];

        $response = $this->actingAs($this->educationManager)->post(route('api.activities.store'), $payload);

        $response->assertRedirect(route('activities.index'));
        $this->assertDatabaseHas('activity_instrukturs', [
            'user_id' => $this->educationManager->id,
            'activity' => 'Pengembangan Kurikulum AI',
            'activity_type' => 'Sharing Knowledge',
        ]);
    }

    public function test_education_manager_can_fetch_activities_calendar_data(): void
    {
        session(['last_activity' => time()]);

        ActivityInstruktur::create([
            'user_id' => $this->educationManager->id,
            'activity' => 'Sharing Knowledge AI',
            'activity_type' => 'Sharing Knowledge',
            'activity_date' => now()->format('Y-m-d'),
            'desc' => 'Sesi 1',
        ]);

        $response = $this->actingAs($this->educationManager)->get(route('api.activities', [
            'start' => now()->startOfMonth()->format('Y-m-d'),
            'end' => now()->endOfMonth()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
    }

    public function test_education_manager_can_update_activity_proof_and_description(): void
    {
        session(['last_activity' => time()]);

        $activity = ActivityInstruktur::create([
            'user_id' => $this->educationManager->id,
            'activity' => 'Sharing Knowledge Original',
            'activity_type' => 'Sharing Knowledge',
            'activity_date' => now()->format('Y-m-d'),
            'desc' => 'Deskripsi lama',
        ]);

        $payload = [
            'activity_id' => $activity->id,
            'activity' => 'Sharing Knowledge Updated',
            'activity_type' => 'Sharing Knowledge',
            'desc' => 'Deskripsi diperbarui',
            'doc' => 'https://drive.google.com/file/d/testproof/view',
        ];

        $response = $this->actingAs($this->educationManager)->post(route('api.activities.proof_update'), $payload);

        $response->assertRedirect(route('activities.index'));
        $this->assertDatabaseHas('activity_instrukturs', [
            'id' => $activity->id,
            'activity' => 'Sharing Knowledge Updated',
            'desc' => 'Deskripsi diperbarui',
            'doc' => 'https://drive.google.com/file/d/testproof/view',
        ]);
    }

    public function test_education_manager_can_fetch_summary_data(): void
    {
        session(['last_activity' => time()]);

        ActivityInstruktur::create([
            'user_id' => $this->educationManager->id,
            'activity' => 'Summary Activity Test',
            'activity_type' => 'Sharing Knowledge',
            'activity_date' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->educationManager)->get(route('api.activities.summary', [
            'start' => now()->startOfMonth()->format('Y-m-d'),
            'end' => now()->endOfMonth()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure(['manual_summary', 'rkm_summary']);
    }
}
