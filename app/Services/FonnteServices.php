<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FonnteService
{
    protected $url;
    protected $apiKey;

    public function __construct()
    {
        $this->url = env('FONNTE_URL');
        $this->apiKey = env('FONNTE_API_KEY');
    }

    public function sendMessage($target, $message)
    {
        $response = Http::withHeaders([
            'Authorization' => $this->apiKey,
        ])->post($this->url, [
            'target'  => $target,   // contoh: 6281234567890
            'message' => $message,
        ]);

        return $response->json();
    }
}
