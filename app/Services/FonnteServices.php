<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FonnteService
{
    protected $url;
    protected $apiKey;

    public function __construct()
    {
        $this->url = config('services.fonnte.url');
        $this->apiKey = config('services.fonnte.api_key');
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
