<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use Exception;

class MoodleApiController extends Controller
{
    public function fetchGradesSharingKnowledge(): JsonResponse
    {
        try {
            // Ekstraksi Variabel Environment
            $apiUrl = env('MOODLE_API_URL');
            $apiUsername = env('MOODLE_API_USERNAME');
            $apiPassword = env('MOODLE_API_PASSWORD');

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
