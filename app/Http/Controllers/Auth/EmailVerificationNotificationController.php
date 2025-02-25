<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Check if token exists and user is authenticated
            if (!$user) {
                return response()->json([
                    'message' => 'Unauthenticated',
                ], 401);
            }

            // Check if email is already verified
            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'Email is already verified'
                ], 200);
            }

            // Send verification email
            $user->sendEmailVerificationNotification();

            return response()->json([
                'message' => 'Verification link sent successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send verification email',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
