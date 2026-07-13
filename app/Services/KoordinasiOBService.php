<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\KoordinasiOfficeBoy;
use App\Models\TrackingKoordinasiOfficeBoy;
use App\Models\karyawan;

class KoordinasiOBService
{
    private $botToken;
    private $groupId;

    public function __construct()
    {
        $this->botToken = '8637052174:AAFSALsROZZSHz-fr2PM0IWe-EsYatdYXvI'; 
        $this->groupId = '-5410138806';
    }

    public function createKoordinasi(array $data, $createdBy)
    {
        $koordinasi = KoordinasiOfficeBoy::create([
            'nama_tugas' => $data['nama_tugas'],
            'karyawan'   => $data['karyawan'],
            'deadline'   => $data['deadline'],
            'catatan'    => $data['catatan'] ?? null,
            'created_by' => $createdBy,
            'status'     => 'Menunggu Konfirmasi',
        ]);

        // Tracking
        TrackingKoordinasiOfficeBoy::create([
            'koordinasi_id' => $koordinasi->id,
            'status'        => 'Koordinasi OB dibuat',
            'updated_by'    => $createdBy
        ]);

        $this->sendCreateNotification($koordinasi);

        return $koordinasi;
    }

    public function updateKoordinasi(int $id, array $data, $updatedBy)
    {
        $koordinasi = KoordinasiOfficeBoy::findOrFail($id);
        
        $koordinasi->update([
            'nama_tugas' => $data['nama_tugas'],
            'karyawan'   => $data['karyawan'],
            'deadline'   => $data['deadline'],
            'catatan'    => $data['catatan'] ?? $koordinasi->catatan,
        ]);

        TrackingKoordinasiOfficeBoy::create([
            'koordinasi_id' => $koordinasi->id,
            'status'        => 'Koordinasi OB diupdate',
            'updated_by'    => $updatedBy
        ]);

        return $koordinasi;
    }

    public function updateStatus(string $action, int $id, $userId = null)
    {
        $koordinasi = KoordinasiOfficeBoy::findOrFail($id);
        $namaOb = karyawan::findOrFail($koordinasi->karyawan);

        $telegramPayload = [];

        if ($action === 'terima') {
            if ($koordinasi->status === 'Menunggu Konfirmasi') {
                $koordinasi->update(['status' => 'Dikerjakan']);

                TrackingKoordinasiOfficeBoy::create([
                    'koordinasi_id' => $koordinasi->id,
                    'status'        => 'Tugas sedang dikerjakan',
                    'updated_by'    => $userId ?? $koordinasi->karyawan
                ]);

                $telegramPayload = [
                    'title' => '✅ Tugas Diterima',
                    'id_pengajuan' => $koordinasi->id,
                    'ob_name' => $namaOb->nama_lengkap,
                    'status' => 'Dikerjakan'
                ];
            }
        } elseif ($action === 'selesai') {
            if ($koordinasi->status !== 'Selesai') {
                $koordinasi->update(['status' => 'Selesai']);

                TrackingKoordinasiOfficeBoy::create([
                    'koordinasi_id' => $koordinasi->id,
                    'status'        => 'Tugas selesai',
                    'updated_by'    => $userId ?? $koordinasi->karyawan
                ]);

                $telegramPayload = [
                    'title' => '🏁 Tugas Selesai',
                    'id_pengajuan' => $koordinasi->id,
                    'ob_name' => $namaOb->nama_lengkap,
                    'status' => 'Selesai'
                ];
            }
        } else {
            Log::warning("Action tidak dikenali di updateStatus: " . $action);
        }

        if (!empty($telegramPayload)) {
            Log::info("Mengirim update status ke Telegram", [
                'action' => $action,
                'id' => $id,
                'title' => $telegramPayload['title']
            ]);
            
            $this->updateStatusTelegram($telegramPayload);
        } else {
            Log::info("Tidak ada payload Telegram untuk action: " . $action);
        }

        return $koordinasi;
    }

    public function sendCreateNotification(KoordinasiOfficeBoy $koordinasi)
    {
        $namaOb = karyawan::findOrFail($koordinasi->karyawan);
        $creator = $koordinasi->pembuat;

        $payload = [
            'title'        => '🔔 Koordinasi OB Baru',
            'id_pengajuan' => $koordinasi->id,
            'nama_tugas'   => $koordinasi->nama_tugas,
            'creator_name' => $creator?->karyawan?->nama_lengkap ?? 'System',
            'ob_name'      => $namaOb->nama_lengkap,
            'deadline'     => $koordinasi->deadline,
            'status'       => $koordinasi->status,
            'catatan'      => $koordinasi->catatan ?? '-'
        ];

        $this->sendToTelegram($payload);
    }

    public function sendDeleteNotification(KoordinasiOfficeBoy $koordinasi)
    {
        $namaOb = karyawan::findOrFail($koordinasi->karyawan);

        $payload = [
            'title'        => '🗑️ Tugas ' . $koordinasi->nama_tugas . ' dihapus',
            'id_pengajuan' => $koordinasi->id,
            'ob_name'      => $namaOb->nama_lengkap,
            'status'       => 'Dihapus'
        ];

        $this->updateStatusTelegram($payload);
    }

    public function deleteKoordinasi(int $id, int $deletedBy)
    {
        $koordinasi = KoordinasiOfficeBoy::findOrFail($id);

        TrackingKoordinasiOfficeBoy::create([
            'koordinasi_id' => $koordinasi->id,
            'status'        => 'Koordinasi OB dihapus',
            'updated_by'    => $deletedBy
        ]);

        $this->sendDeleteNotification($koordinasi);

        return $koordinasi->delete();
    }

    public function sendToTelegram(array $data)
    {
        $time = isset($data['deadline']) ? \Carbon\Carbon::parse($data['deadline'])->format('d M Y, H:i') : '-';

        $message = "*{$data['title']}*\n\n" . "ID: `#{$data['id_pengajuan']}`\n" . "Dibuat: {$data['creator_name']}\n" . "Office Boy: {$data['ob_name']}\n" . "Tugas: {$data['nama_tugas']}\n" . "Deadline: {$time}\n" . "Status: {$data['status']}\n" . "──────────────────────\n" . "Catatan: {$data['catatan']}\n\n" . 'Silahkan terima koordinasi melalui Inixcoffee atau tombol terima dibawah ini';

        return Http::timeout(5)->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
            'chat_id' => $this->groupId,
            'text' => $message,
            'parse_mode' => 'Markdown',
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        [
                            'text' => '🔍 Lihat Detail',
                            'url' => 'https://coffee.inixindobdg.co.id/office/koordinasi-ob',
                        ],
                        [
                            'text' => '✅ Terima',
                            'callback_data' => "terima:{$data['id_pengajuan']}",
                        ],
                    ],
                    [
                        [
                            'text' => '🏁 Selesai',
                            'callback_data' => "selesai:{$data['id_pengajuan']}",
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function updateStatusTelegram(array $data)
    {
        $message = "*{$data['title']}*\n\n" . "ID: `#{$data['id_pengajuan']}`\n" . "Office Boy: {$data['ob_name']}\n" . "Status: {$data['status']}\n\n";

        return Http::timeout(5)->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
            'chat_id' => $this->groupId,
            'text' => $message,
            'parse_mode' => 'Markdown',
        ]);
    }
}
