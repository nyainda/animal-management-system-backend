<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification to the authenticated user.
     *
     * @OA\Post(
     *     path="/api/email/verification-notification",
     *     tags={"Authentication"},
     *     summary="Send email verification notification",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verification link sent or email already verified",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Verification link sent successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="User not authenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to send verification email"),
     *             @OA\Property(property="error", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Check if user is authenticated
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'message' => 'Unauthenticated'
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
            Log::error('Failed to send verification email: ' . $e->getMessage(), [
                'user_id' => $request->user() ? $request->user()->id : null,
                'request' => $request->all()
            ]);
            return response()->json([
                'message' => 'Failed to send verification email',
                'error' => 'Internal server error'
            ], 500);
        }
    }
}