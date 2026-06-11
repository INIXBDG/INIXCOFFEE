<?php

namespace App\Services;

use App\Models\pickupDriver;
use App\Models\karyawan;
use App\Models\TrackingPickupDriver;
use App\Models\User;
use App\Http\Controllers\TelegramController;

class PickupDriverTelegramService
{
    public function terimaKoordinasi($id)
    {
        $pickupDriver = pickupDriver::with('detailPickupDriver')
            ->findOrFail($id);

        if ($pickupDriver->status_apply != 0) {
            return [
                'success' => false,
                'message' => '⚠️ Status koordinasi sudah berubah.'
            ];
        }

        $detail = $pickupDriver->detailPickupDriver->first();

        if (!$detail) {
            return [
                'success' => false,
                'message' => 'Detail pickup tidak ditemukan.'
            ];
        }

        if ($detail->tipe === 'Penjemputan') {
            $pickupDriver->status_driver = 'Sedang Menjemput';
            $statusDriver = 'Sedang Menjemput';
        } elseif ($detail->tipe === 'Pengantaran') {
            $pickupDriver->status_driver = 'Sedang Mengantarkan';
            $statusDriver = 'Sedang Mengantarkan';
        } else {
            $pickupDriver->status_driver = 'Diterima';
            $statusDriver = 'Diterima';
        }

        $pickupDriver->status_apply = 1;
        $pickupDriver->save();

        $driver = karyawan::find($pickupDriver->id_karyawan);
        $detailTipe = $pickupDriver->detailPickupDriver->pluck('tipe')->toArray();

        /*
        |--------------------------------------------------------------------------
        | Tracking
        |--------------------------------------------------------------------------
        */
        TrackingPickupDriver::create([
            'pickup_driver_id' => $pickupDriver->id,
            'status' => 'Koordinasi diterima melalui Telegram, status menjadi ' .
                $statusDriver .
                ' dengan kendaraan ' .
                ($pickupDriver->kendaraan ?? '-'),
            'diubah_oleh' => $driver->id,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Telegram Notification
        |--------------------------------------------------------------------------
        */
        $telegramPayload = [
            'title' => '🔄 Status Diperbarui',
            'id_pengajuan' => $pickupDriver->id,
            'creator_name' => 'Telegram Bot',
            'driver_name' => $driver->nama_lengkap ?? '-',
            'budget' => $pickupDriver->budget,
            'tanggal_pembuatan' => now(),
            'status_text' => $statusDriver,
            'status_apply' => $pickupDriver->status_apply,
            'tipe' => $detailTipe,
            'lokasi' => [],
            'tanggal' => [],
            'waktu' => [],
            'detail' => [],
            'log_text' => null,
            'path' => '/office/pickup-driver/index',
        ];

        $telegramCtrl = new TelegramController();
        $personalData = $telegramCtrl->formatPersonalCoordinationMessage($telegramPayload);
        $telegramCtrl->sendPersonalTelegramMessage($personalData);

        return [
            'success' => true,
            'message' => '✅ Koordinasi berhasil diterima!'
        ];
    }

    public function selesaikanKoordinasi($id)
    {
        $pickupDriver = pickupDriver::findOrFail($id);

        if ($pickupDriver->status_apply != 1) {
            return [
                'success' => false,
                'message' => '⚠️ Koordinasi belum dalam status Diterima.'
            ];
        }

        $pickupDriver->status_apply = 2;
        $pickupDriver->status_driver = 'Selesai, Driver Ready';
        $pickupDriver->waktu_kepulangan = now();

        $pickupDriver->save();

        $driver = karyawan::find($pickupDriver->id_karyawan);

        $detailTipe = $pickupDriver->detailPickupDriver()
            ->pluck('tipe')
            ->toArray();

        $telegramPayload = [
            'title' => '🏁 Koordinasi Selesai',
            'id_pengajuan' => $pickupDriver->id,
            'creator_name' => 'Telegram Bot',
            'driver_name' => $driver->nama_lengkap ?? '-',
            'budget' => $pickupDriver->budget,
            'tanggal_pembuatan' => now(),
            'status_text' => 'Selesai, Driver Ready',
            'status_apply' => $pickupDriver->status_apply,
            'tipe' => $detailTipe,
            'lokasi' => [],
            'tanggal' => [],
            'waktu' => [],
            'detail' => [],
            'log_text' => null,
        ];

        $telegramCtrl = new TelegramController();

        $personalData =
            $telegramCtrl->formatPersonalCoordinationMessage(
                $telegramPayload
            );

        $telegramCtrl->sendPersonalTelegramMessage(
            $personalData
        );

        return [
            'success' => true,
            'message' => '🏁 Koordinasi berhasil diselesaikan!'
        ];
    }
}