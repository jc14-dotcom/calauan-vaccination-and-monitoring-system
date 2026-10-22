<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Auth\SessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Session Controller
 * 
 * Handles API endpoints for session verification and authentication status checks.
 * Used by client-side JavaScript to verify session validity.
 */
class SessionController extends Controller
{
    /**
     * Session service instance
     */
    protected SessionService $sessionService;
    
    /**
     * Create a new controller instance.
     */
    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }
    
    /**
     * Check current session authentication status
     * 
     * This endpoint is called by client-side JavaScript (session-guard.js)
     * to verify that the user's session is still valid. It checks all guards
     * and returns the authentication status.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function check(Request $request): JsonResponse
    {
        $result = $this->sessionService->checkAuthentication();
        
        return response()->json([
            'authenticated' => $result['authenticated'],
            'guard' => $result['guard'],
            'debug' => $result['debug'],
            'timestamp' => now()->toDateTimeString(),
        ], 200, [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
