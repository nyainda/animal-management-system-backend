<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetLinkController extends Controller
{
    /**
     * Send a password reset link to the provided email.
     *
     * @OA\Post(
     *     path="/api/forgot-password",
     *     tags={"Authentication"},
     *     summary="Request a password reset link",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com", description="The email address to send the reset link to")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset link sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password reset link sent successfully"),
     *             @OA\Property(property="status", type="string", example="We have emailed your password reset link!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed or email not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to send reset link"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="email",
     *                     type="array",
     *                     @OA\Items(type="string", example="We can't find a user with that email address.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to process request"),
     *             @OA\Property(property="error", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validate the request
            $request->validate([
                'email' => ['required', 'email'],
            ]);

            // Send the password reset link
            $status = Password::sendResetLink(
                $request->only('email')
            );

            // Check the status and respond accordingly
            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'message' => 'Password reset link sent successfully',
                    'status' => __($status)
                ], 200);
            }

            // If reset link failed (e.g., email not found), treat as validation error
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Failed to send reset link',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to process password reset link request: ' . $e->getMessage(), [
                'request' => $request->all()
            ]);
            return response()->json([
                'message' => 'Failed to process request',
                'error' => 'Internal server error'
            ], 500);
        }
    }
}