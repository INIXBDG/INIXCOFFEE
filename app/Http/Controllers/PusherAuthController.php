<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PusherAuthController extends Controller
{
    public function auth(Request $request)
    {
        // BACA DARI SEMUA SUMBER: input, query, json, form-data
        $channelName = $request->input('channel_name') 
                    ?? $request->query('channel_name')
                    ?? $request->json('channel_name');

        $socketId = $request->input('socket_id') 
                 ?? $request->query('socket_id')
                 ?? $request->json('socket_id');

        if (!$channelName || !$socketId) {
            return response()->json(['message' => 'Missing params'], 400);
        }

        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated'], 403);
        }

        // Cocokkan channel
        if (!preg_match('/^private-notifikasi\.(\d+)$/', $channelName, $matches)) {
            return response()->json(['message' => 'Invalid channel'], 403);
        }

        if ((int)$matches[1] !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Manual auth Pusher
        $pusher = config('broadcasting.connections.pusher');
        $string = "$socketId:$channelName";
        $signature = hash_hmac('sha256', $string, $pusher['secret']);

        return response()->json([
            'auth' => $pusher['key'] . ':' . $signature
        ]);
    }
}