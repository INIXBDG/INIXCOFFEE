<?php

namespace Tests\Feature;

use App\Models\Tickets;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramTicketingTest extends TestCase
{
    use DatabaseTransactions;

    private function postWebhook(array $payload)
    {
        return $this->withHeader(
            'X-Telegram-Bot-Api-Secret-Token',
            (string) env('TELEGRAM_WEBHOOK_SECRET')
        )->postJson('/api/telegram/webhook', $payload);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
    }

    private function createTicket(string $ticketId): Tickets
    {
        return Tickets::create([
            'ticket_id' => $ticketId,
            'nama_karyawan' => 'Telegram Test User',
            'divisi' => 'IT',
            'kategori' => 'Hardware',
            'keperluan' => 'Technical Support',
            'detail_kendala' => 'Test ticket from Telegram',
            'timestamp' => now(),
            'status' => 'Menunggu',
        ]);
    }

    public function test_telegram_command_accepts_ticket(): void
    {
        $ticket = $this->createTicket('TG-ACCEPT-'.uniqid());

        $response = $this->postWebhook([
            'message' => [
                'from' => ['id' => 8019408343, 'first_name' => 'Telegram User'],
                'text' => '/terima '.$ticket->ticket_id,
            ],
        ]);

        $response->assertOk()->assertJsonPath('status', 'text_processed');
        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'status' => 'Di Proses',
            'pic' => 'Ravael',
        ]);
    }

    public function test_telegram_button_rejects_ticket(): void
    {
        $ticket = $this->createTicket('TG-REJECT-'.uniqid());

        $response = $this->postWebhook([
            'callback_query' => [
                'id' => 'callback-'.uniqid(),
                'from' => ['id' => 2021670238, 'first_name' => 'Telegram User'],
                'data' => 'reject:'.$ticket->ticket_id,
                'message' => [
                    'chat' => ['id' => -100123],
                    'message_id' => 10,
                ],
            ],
        ]);

        $response->assertOk()->assertJsonPath('status', 'callback_processed');
        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'status' => 'Terkendala',
            'pic' => null,
        ]);
    }

    public function test_telegram_command_finishes_ticket(): void
    {
        $ticket = $this->createTicket('TG-FINISH-'.uniqid());

        $response = $this->postWebhook([
            'message' => [
                'from' => ['id' => 1564401546, 'first_name' => 'Telegram User'],
                'text' => '/selesai '.$ticket->ticket_id.' Masalah sudah diperbaiki',
            ],
        ]);

        $response->assertOk()->assertJsonPath('status', 'text_processed');
        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'status' => 'Selesai',
            'keterangan' => 'Masalah sudah diperbaiki',
        ]);
    }
}
