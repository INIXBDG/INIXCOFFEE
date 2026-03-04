<?php

namespace App\Http\Controllers;

use App\Services\WebPushService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebPushController extends Controller
{
    protected $webPushService;

    public function __construct(WebPushService $webPushService)
    {
        $this->webPushService = $webPushService;
    }

    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'endpoint'       => 'required|string',
            'keys'           => 'required|array',
            'keys.p256dh'    => 'required|string',
            'keys.auth'      => 'required|string',
            'contentEncoding' => 'nullable|string',
        ]);

        $result = $this->webPushService->subscribe(Auth::id(), $validated);

        return response()->json($result);
    }

    public function unsubscribe(Request $request)
    {
        $validated = $request->validate(['endpoint' => 'required|string']);

        $result = $this->webPushService->unsubscribe($validated['endpoint']);

        return response()->json($result);
    }

    public function subscriptionStatus()
    {
        $isSubscribed = $this->webPushService->userHasActiveSubscription();

        return response()->json([
            'success'    => true,
            'subscribed' => $isSubscribed,
        ]);
    }

    public function getVapidKey()
    {
        return response()->json([
            'publicKey' => $this->webPushService->getVapidPublicKey()
        ]);
    }

    public function testNotification(Request $request)
    {
        // kode test notification kamu tetap di sini
    }
}