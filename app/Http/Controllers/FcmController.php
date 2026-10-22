<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FcmController extends Controller
{
    /**
     * Subscribe a parent to FCM notifications
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function subscribe(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required|string|min:50'
            ]);
            
            $parent = Auth::guard('parents')->user();
            
            if (!$parent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }
            
            // Save FCM token to parent record
            $parent->fcm_token = $request->token;
            $parent->save();
            
            Log::info('FCM token subscribed', [
                'parent_id' => $parent->id,
                'parent_name' => $parent->name,
                'token' => substr($request->token, 0, 20) . '...'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'FCM token saved successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('FCM subscription failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save FCM token'
            ], 500);
        }
    }
    
    /**
     * Unsubscribe a parent from FCM notifications
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unsubscribe(Request $request)
    {
        try {
            $parent = Auth::guard('parents')->user();
            
            if (!$parent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }
            
            // Remove FCM token
            $parent->fcm_token = null;
            $parent->save();
            
            Log::info('FCM token unsubscribed', [
                'parent_id' => $parent->id,
                'parent_name' => $parent->name
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'FCM token removed successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('FCM unsubscription failed', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove FCM token'
            ], 500);
        }
    }
    
    /**
     * Get FCM configuration for frontend
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConfig()
    {
        return response()->json([
            'apiKey' => config('services.fcm.api_key'),
            'authDomain' => config('services.fcm.auth_domain'),
            'projectId' => config('services.fcm.project_id'),
            'storageBucket' => config('services.fcm.storage_bucket'),
            'messagingSenderId' => config('services.fcm.sender_id'),
            'appId' => config('services.fcm.app_id'),
            'vapidKey' => config('services.fcm.web_push_certificate'),
        ]);
    }
}
