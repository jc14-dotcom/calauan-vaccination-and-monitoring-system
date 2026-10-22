<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushSubscriptionController extends Controller
{
    /**
     * Subscribe to push notifications.
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        // Check for authenticated user from any guard
        $user = Auth::guard('parents')->user() ?? Auth::guard('health_worker')->user();
        
        if (!$user) {
            return response()->json([
                'error' => 'Unauthenticated',
                'message' => 'Please log in to subscribe to notifications'
            ], 401);
        }

        // Delete existing subscriptions with the same endpoint to prevent duplicates
        $user->pushSubscriptions()->where('endpoint', $validated['endpoint'])->delete();

        // Create new subscription
        $user->updatePushSubscription(
            $validated['endpoint'],
            $validated['keys']['p256dh'],
            $validated['keys']['auth']
        );

        return response()->json([
            'success' => true,
            'message' => 'Successfully subscribed to push notifications.'
        ]);
    }

    /**
     * Unsubscribe from push notifications.
     */
    public function unsubscribe(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
        ]);

        // Check for authenticated user from any guard
        $user = Auth::guard('parents')->user() ?? Auth::guard('health_worker')->user();
        
        if (!$user) {
            return response()->json([
                'error' => 'Unauthenticated',
                'message' => 'Please log in to unsubscribe'
            ], 401);
        }

        $user->pushSubscriptions()->where('endpoint', $validated['endpoint'])->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully unsubscribed from push notifications.'
        ]);
    }

    /**
     * Get VAPID public key for client-side subscription.
     */
    public function getPublicKey()
    {
        // Get the VAPID public key and clean it (remove line breaks, spaces)
        $publicKey = config('webpush.vapid.public_key');
        $cleanedKey = preg_replace('/[\r\n\s]/', '', $publicKey);
        
        return response()->json([
            'publicKey' => $cleanedKey
        ]);
    }

    /**
     * Test push notification (for development).
     */
    public function testPush()
    {
        // Check for authenticated user from any guard
        $user = Auth::guard('parents')->user() ?? Auth::guard('health_worker')->user();
        
        if (!$user) {
            return response()->json([
                'error' => 'Unauthenticated',
                'message' => 'Please log in to test notifications'
            ], 401);
        }

        // Send a test notification
        $user->notify(new \Illuminate\Notifications\Messages\SimpleNotification([
            'title' => 'Test Push Notification',
            'body' => 'Ito ay isang test notification mula sa Infant Vaccination System.',
            'icon' => '/images/icon-192x192.png',
            'action_url' => url('/'),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Test notification sent.'
        ]);
    }
}
