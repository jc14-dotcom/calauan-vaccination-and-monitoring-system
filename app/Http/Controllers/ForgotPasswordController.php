<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;

class ForgotPasswordController extends Controller
{
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function handleForgotPassword(Request $request)
    {
        \Log::info('Password reset request received:', ['email' => $request->input('email')]);

        try {
            $request->validate(['email' => 'required|email']);

            $email = $request->input('email');
            
            // Try the 'parents' broker first
            $status = Password::broker('parents')->sendResetLink(['email' => $email]);

            // If the 'parents' broker fails, try the 'health_workers' broker
            if ($status !== Password::RESET_LINK_SENT) {
                \Log::info('Parents broker failed, trying health_workers broker');
                $status = Password::broker('health_workers')->sendResetLink(['email' => $email]);
            }

            \Log::info('Password reset status:', ['status' => $status]);

            // Return user-friendly messages
            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'status' => true,
                    'message' => 'Password reset link has been sent to your email address. Please check your inbox.'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'We couldn\'t find an account with that email address. Please check and try again.'
                ]);
            }
        } catch (\Swift_TransportException $e) {
            \Log::error('Email sending failed:', [
                'error' => $e->getMessage(),
                'email_config' => [
                    'mailer' => config('mail.mailer'),
                    'host' => config('mail.host'),
                    'port' => config('mail.port'),
                ]
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Unable to send email at this time. Please contact the system administrator or try again later.'
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Error in handleForgotPassword:', [
                'message' => $e->getMessage(),
                'type' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'An unexpected error occurred. Please contact the system administrator.'
            ], 500);
        }
    }

    public function handleReset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@#$%^&*()_+\-=\[\]{}|;:,.<>?]).{8,}$/'
            ],
        ]);

        // Try resetting password with the 'parents' broker
        $status = Password::broker('parents')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ]);

                $user->save();

                event(new PasswordReset($user));
            }
        );

        // If the 'parents' broker fails, try the 'health_workers' broker
        if ($status !== Password::PASSWORD_RESET) {
            $status = Password::broker('health_workers')->reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ]);

                    $user->save();

                    event(new PasswordReset($user));
                }
            );
        }

        return response()->json([
            'status' => $status === Password::PASSWORD_RESET,
            'message' => __($status)
        ]);
    }
}