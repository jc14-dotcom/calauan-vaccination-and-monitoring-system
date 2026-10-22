<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    protected $projectId;
    protected $credentialsPath;
    
    public function __construct()
    {
        $this->credentialsPath = storage_path('app/' . config('services.fcm.credentials_path'));
        
        // Get project ID from credentials file
        $credentials = json_decode(file_get_contents($this->credentialsPath), true);
        $this->projectId = $credentials['project_id'];
    }

    /**
     * Get OAuth2 access token from service account
     */
    protected function getAccessToken()
    {
        // Read service account credentials
        $credentialsData = json_decode(file_get_contents($this->credentialsPath), true);
        
        // Create JWT
        $now = time();
        $jwtPayload = [
            'iss' => $credentialsData['client_email'],
            'sub' => $credentialsData['client_email'],
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging'
        ];
        
        $jwt = $this->createJWT($jwtPayload, $credentialsData['private_key']);
        
        // Exchange JWT for access token using Laravel Http (respects custom SSL cert)
        $certPath = 'C:\laragon\etc\ssl\cacert.pem';
        $httpOptions = [];
        if (app()->environment('local') && file_exists($certPath)) {
            $httpOptions['verify'] = $certPath;
        }
        
        $response = Http::withOptions($httpOptions)->asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]);
        
        if (!$response->successful()) {
            throw new \Exception('Failed to get access token: ' . $response->body());
        }
        
        return $response->json()['access_token'];
    }
    
    /**
     * Create JWT for service account authentication
     */
    protected function createJWT($payload, $privateKey)
    {
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT'
        ];
        
        $segments = [];
        $segments[] = $this->base64UrlEncode(json_encode($header));
        $segments[] = $this->base64UrlEncode(json_encode($payload));
        $signingInput = implode('.', $segments);
        
        openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $segments[] = $this->base64UrlEncode($signature);
        
        return implode('.', $segments);
    }
    
    /**
     * Base64 URL encode
     */
    protected function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Send push notification via FCM v1 API
     * 
     * @param string $fcmToken The FCM device token
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @return array Success status and response
     */
    public function send($fcmToken, $title, $body, $data = [])
    {
        try {
            $accessToken = $this->getAccessToken();
            $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
            
            // Configure SSL certificate for Laragon
            $certPath = 'C:\laragon\etc\ssl\cacert.pem';
            $httpOptions = [];
            if (app()->environment('local') && file_exists($certPath)) {
                $httpOptions['verify'] = $certPath;
            }
            
            $response = Http::withOptions($httpOptions)->withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'message' => [
                    'token' => $fcmToken,
                    // DATA-ONLY message - no 'notification' payload
                    // This prevents Chrome from auto-displaying the notification
                    // Our service worker will handle display instead
                    'data' => array_merge([
                        'title' => $title,
                        'body' => $body,
                        'icon' => url('/images/icon-192x192.png'),
                        'click_action' => url('/parents/parentdashboard'),
                    ], $data),
                ]
            ]);

            if ($response->successful()) {
                Log::info('FCM notification sent successfully', [
                    'token' => substr($fcmToken, 0, 20) . '...',
                    'title' => $title
                ]);
                return ['success' => true, 'response' => $response->json()];
            }

            Log::error('FCM notification failed', [
                'token' => substr($fcmToken, 0, 20) . '...',
                'error' => $response->json()
            ]);
            return ['success' => false, 'error' => $response->body()];
            
        } catch (\Exception $e) {
            Log::error('FCM exception occurred', [
                'token' => substr($fcmToken, 0, 20) . '...',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send to multiple tokens
     * Note: FCM v1 API doesn't support batch in one call, so we loop
     * 
     * @param array $tokens Array of FCM device tokens
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @return array Results for each token
     */
    public function sendMultiple(array $tokens, $title, $body, $data = [])
    {
        $results = [];
        $successCount = 0;
        $failureCount = 0;
        
        foreach ($tokens as $token) {
            $result = $this->send($token, $title, $body, $data);
            $results[] = $result;
            
            if ($result['success']) {
                $successCount++;
            } else {
                $failureCount++;
            }
        }
        
        Log::info('FCM batch send completed', [
            'total' => count($tokens),
            'success' => $successCount,
            'failed' => $failureCount
        ]);
        
        return [
            'total' => count($tokens),
            'success' => $successCount,
            'failed' => $failureCount,
            'results' => $results
        ];
    }

    /**
     * Validate FCM token format
     * 
     * @param string $token
     * @return bool
     */
    public function isValidToken($token)
    {
        return !empty($token) && is_string($token) && strlen($token) > 50;
    }
}
