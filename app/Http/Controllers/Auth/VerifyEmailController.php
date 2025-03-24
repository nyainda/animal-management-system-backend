<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyEmailController extends Controller
{
    /**
     * Verify the authenticated user's email address.
     *
     * @OA\Get(
     *     path="/api/verify-email/{id}/{hash}",
     *     tags={"Authentication"},
     *     summary="Verify the user's email address",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the user",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Parameter(
     *         name="hash",
     *         in="path",
     *         required=true,
     *         description="The hash from the verification link",
     *         @OA\Schema(type="string", example="abc123def456")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email verification status",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Email verified successfully"),
     *             @OA\Property(property="verified", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="User not authenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not authenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Email verification failed"),
     *             @OA\Property(property="error", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function __invoke(Request $request): JsonResponse
    {
        try {
            // Ensure the user is authenticated
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Check if email is already verified
            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'Email already verified',
                    'verified' => true
                ], 200);
            }

            // Verify the email
            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }

            return response()->json([
                'message' => 'Email verified successfully',
                'verified' => true
            ], 200);

        } catch (\Exception $e) {
            Log::error('Email verification failed: ' . $e->getMessage(), [
                'user_id' => $request->user() ? $request->user()->id : null,
                'request' => $request->all()
            ]);
            return response()->json([
                'message' => 'Email verification failed',
                'error' => 'Internal server error'
            ], 500);
        }
    }
}