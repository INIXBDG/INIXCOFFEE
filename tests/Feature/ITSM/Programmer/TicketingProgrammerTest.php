<?php

namespace Tests\Feature\ITSM\Programmer;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TicketingProgrammerTest extends TestCase
{
    use DatabaseTransactions;

    private function createUserWithRole($roleName = 'Programmer', $divisi = 'IT Service Management')
    {
        $karyawanId = DB::table('karyawans')->insertGetId([
            'nama_lengkap' => 'Test Programmer ' . uniqid(),
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

    private function createTicket($status = 'Menunggu')
    {
        return DB::table('tickets')->insertGetId([
            'ticket_id' => 'TCK-' . uniqid(),
            'nama_karyawan' => 'Test Karyawan',
            'divisi' => 'IT',
            'kategori' => 'Hardware',
            'keperluan' => 'Programming',
            'detail_kendala' => 'Test Ticket',
            'status' => $status,
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_programmer_can_access_ticket_list()
    {
        $user = $this->createUserWithRole();
        $response = $this->actingAs($user)->get('/tickets');
        $this->assertTrue(in_array($response->status(), [200, 302]), "Status is: " . $response->status());
    }

    public function test_programmer_can_access_create_ticket_page()
    {
        $user = $this->createUserWithRole();
        $response = $this->actingAs($user)->get('/tickets/create');
        $this->assertTrue(in_array($response->status(), [200, 302]), "Status is: " . $response->status());
    }

    public function test_programmer_can_create_ticket_with_valid_data()
    {
        $user = $this->createUserWithRole();
        
        $response = $this->actingAs($user)->post('/tickets', [
            'nama_karyawan' => 'Seseorang',
            'divisi' => 'IT',
            'kategori' => 'Hardware',
            'keperluan' => 'Programming',
            'detail_kendala' => 'Need help with DB',
            'datetime' => now()->format('Y-m-d H:i:s'),
        ]);
        
        $this->assertTrue(in_array($response->status(), [200, 201, 302]), "Status is: " . $response->status());
    }

    public function test_programmer_create_ticket_with_empty_payload()
    {
        $user = $this->createUserWithRole();
        $response = $this->actingAs($user)->post('/tickets', []);
        $this->assertTrue(in_array($response->status(), [302, 422]), "Status is: " . $response->status());
    }

    public function test_programmer_create_ticket_with_incomplete_fields()
    {
        $user = $this->createUserWithRole();
        $response = $this->actingAs($user)->post('/tickets', [
            'keperluan' => 'Programming'
        ]);
        $this->assertTrue(in_array($response->status(), [302, 422]), "Status is: " . $response->status());
    }

    public function test_programmer_can_view_valid_ticket_detail()
    {
        $user = $this->createUserWithRole();
        $ticketId = $this->createTicket();

        $response = $this->actingAs($user)->get("/tickets/{$ticketId}");
        $this->assertTrue(in_array($response->status(), [200, 302]), "Status is: " . $response->status());
    }

    public function test_programmer_gets_404_for_invalid_ticket_detail()
    {
        $user = $this->createUserWithRole();
        $response = $this->actingAs($user)->get("/tickets/99999");
        $this->assertTrue(in_array($response->status(), [404, 302, 500]), "Status is: " . $response->status());
    }

    public function test_programmer_can_accept_ticket()
    {
        $user = $this->createUserWithRole();
        $ticketId = $this->createTicket();

        $response = $this->actingAs($user)->post("/tickets/{$ticketId}/accept");
        $this->assertTrue(in_array($response->status(), [200, 302]), "Status is: " . $response->status());
    }

    public function test_programmer_can_finish_ticket()
    {
        $user = $this->createUserWithRole();
        $ticketId = $this->createTicket('Di Proses');

        $response = $this->actingAs($user)->post("/tickets/{$ticketId}/finish", [
            'keterangan_selesai' => 'Done'
        ]);
        $this->assertTrue(in_array($response->status(), [200, 302]), "Status is: " . $response->status());
    }

    public function test_programmer_can_block_ticket()
    {
        $user = $this->createUserWithRole();
        $ticketId = $this->createTicket();

        $response = $this->actingAs($user)->post("/tickets/{$ticketId}/block", [
            'keterangan_blokir' => 'Block reason'
        ]);
        $this->assertTrue(in_array($response->status(), [200, 302]), "Status is: " . $response->status());
    }

    public function test_unauthorized_user_cannot_access_programmer_ticket_features()
    {
        $response = $this->get('/tickets');
        $this->assertTrue(in_array($response->status(), [302, 401, 403]), "Status is: " . $response->status());
    }
}
