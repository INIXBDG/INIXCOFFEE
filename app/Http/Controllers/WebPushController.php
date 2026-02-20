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

    /**
     * Subscribe user ke push notification
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
            'keys' => 'required|array',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
            'contentEncoding' => 'nullable|string',
        ]);

        $result = $this->webPushService->subscribe(
            Auth::id(),
            $validated
        );

        return response()->json($result);
    }

    /**
     * Unsubscribe user
     */
    public function unsubscribe(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|string'
        ]);

        $result = $this->webPushService->unsubscribe($validated['endpoint']);

        return response()->json($result);
    }

    /**
     * Get VAPID public key
     */
    public function getVapidKey()
    {
        return response()->json([
            'publicKey' => $this->webPushService->getVapidPublicKey()
        ]);
    }

    /**
     * Test send notification
     */
    public function testNotification(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
            'userId' => 'nullable|integer'
        ]);

        if ($validated['userId'] ?? null) {
            $result = $this->webPushService->sendNotificationToUser(
                $validated['userId'],
                $validated['title'],
                $validated['body']
            );
        } else {
            $result = $this->webPushService->sendNotificationToAll(
                $validated['title'],
                $validated['body']
            );
        }

        return response()->json($result);
    }
}