<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Exception;

class MoodleApiController extends Controller
{
    public function fetchGradesSharingKnowledge()
    {
        try {
            // Ekstraksi Konfigurasi Moodle API (aman untuk config:cache)
            $apiUrl = config('services.moodle.api_url');
            $apiUsername = config('services.moodle.api_username');
            $apiPassword = config('services.moodle.api_password');

            // Eksekusi Permintaan HTTP GET dengan Basic Authentication
            $response = Http::withBasicAuth($apiUsername, $apiPassword)
                ->timeout(15)
                ->get($apiUrl);

            // Validasi Status Kode Respons HTTP (200-299)
            if ($response->successful()) {
                return response()->json([
                    'status' => 'success',
                    'source' => 'laravel_client',
                    'data' => $response->json()
                ], 200);
            }

            // Pengembalian Respons Gagal dari API Eksternal
            return response()->json([
                'status' => 'error',
                'message' => 'API Request Failed',
                'status_code' => $response->status()
            ], $response->status());

        } catch (Exception $e) {
            // Pengembalian Respons Kesalahan Internal Laravel
            return response()->json([
                'status' => 'error',
                'message' => 'Internal Server Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
