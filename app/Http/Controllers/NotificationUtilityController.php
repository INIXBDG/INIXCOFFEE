<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationUtilityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function fetch(Request $request): JsonResponse
    {
        return response()->json($request->user()->unreadNotifications()->latest()->limit(50)->get());
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function modalContent(Request $request)
    {
        $unreadNotifications = $request->user()
            ->unreadNotifications()
            ->latest()
            ->limit(50)
            ->get();

        return view('partials.notifications', compact('unreadNotifications'));
    }
}
