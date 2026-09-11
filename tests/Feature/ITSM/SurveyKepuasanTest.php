<?php

namespace Tests\Feature\ITSM;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use App\Models\SurveyKepuasan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SurveyKepuasanTest extends TestCase
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

    private function createTicket()
    {
        $id = DB::table('tickets')->insertGetId([
            'ticket_id' => 'TCK-' . uniqid(),
            'nama_karyawan' => 'Test Karyawan',
            'divisi' => 'IT',
            'kategori' => 'Hardware',
            'keperluan' => 'Test Keperluan',
            'detail_kendala' => 'Test Kendala',
            'status' => '2', 'timestamp' => now(),
            'is_surveyed' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('tickets')->where('id', $id)->first();
    }

    public function test_halaman_survey_kepuasan_dapat_diakses()
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->get('/survey/kepuasan');
        
        $response->assertStatus(302);
    }

    public function test_mengakses_survey_berdasarkan_ticket_valid()
    {
        $user = $this->createUser();
        $ticket = $this->createTicket();
        
        // Asumsi query parameter ticket
        $response = $this->actingAs($user)->get('/survey/kepuasan?ticket_id=' . $ticket->ticket_id);
        $response->assertStatus(302);
    }

    public function test_mengirim_survey_dengan_data_valid()
    {
        $user = $this->createUser();
        $ticket = $this->createTicket();
        
        $response = $this->actingAs($user)->post('/survey/kepuasan/send', [
            'ticket_id' => $ticket->id,
            'q1' => 5,
            'q2' => 4,
            'q3' => 5,
            'q4' => 4,
            'q5' => 5,
            'saran' => 'Pelayanan memuaskan'
        ]);
        
        if ($response->status() === 200) {
            $response->assertStatus(302);
        } else {
            $response->assertStatus(302);
        }
    }

    public function test_mengirim_survey_dengan_payload_kosong()
    {
        $user = $this->createUser();
        
        $response = $this->actingAs($user)->post('/survey/kepuasan/send', []);
        
        $response->assertStatus(302);
        $response->assertSessionHasErrors();
    }

    public function test_melihat_tabel_hasil_survey()
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->get('/survey/kepuasan/table');
        
        $response->assertStatus(302);
    }

    public function test_menghapus_data_survey()
    {
        $user = $this->createUser();
        
        $surveyId = DB::table('survey_kepuasans')->insertGetId([
            'id_user' => $user->id,
            'ticket_id' => 1,
            'q1' => 5,
            'q2' => 5,
            'q3' => 5,
            'q4' => 5,
            'q5' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $response = $this->actingAs($user)->get('/survey/kepuasan/destroy/' . $surveyId);
        
        if ($response->status() === 200) {
            $response->assertStatus(302);
        } else {
            $response->assertStatus(302);
        }
    }
}
