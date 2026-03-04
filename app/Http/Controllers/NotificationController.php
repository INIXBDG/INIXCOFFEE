<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function saveSubscription(Request $request)
    {
        $user = auth()->user();
        $endpoint = $request->endpoint;
        $key = $request->keys['p256dh'];
        $token = $request->keys['auth'];

        $user->updatePushSubscription($endpoint, $key, $token);

        return response()->json(['success' => true]);
    }
}
