<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeminiController extends Controller
{
    /**
     * Mengeksekusi permintaan pengujian ke API Gemini menggunakan model Pro.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testGemini(Request $request)
    {
        // Konfigurasi Kredensial dan Model
        $apiKey = env('GEMINI_API_KEY');
        $model = 'gemini-2.5-pro';
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        // Pembuatan Struktur Payload
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => 'Jelaskan protokol HTTP dalam satu kalimat.']
                    ]
                ]
            ]
        ];

        // Eksekusi HTTP Request dengan JSON
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($endpoint, $payload);

        // Eksekusi logika pemrosesan data
        if ($response->successful()) {
            $data = $response->json();
            $responseText = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Respons gagal diekstrak.';

            // Format keluaran yang direstrukturisasi
            return response()->json([
                'status' => 'Berhasil',
                'model' => $model,
                'data' => $responseText
            ], 200);
        }

        // Manajemen keluaran kesalahan peladen
        return response()->json([
            'status' => 'Gagal',
            'detail' => $response->json()
        ], $response->status());
    }
}
