<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class NewPasswordController extends Controller
{
    /**
     * Update the authenticated user's password.
     *
     * @OA\Post(
     *     path="/api/reset-password",
     *     tags={"User"},
     *     summary="Update the authenticated user's password",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="current_password", type="string", example="oldpassword123", description="User's current password"),
     *             @OA\Property(property="new_password", type="string", example="newpassword123", description="New password (min 8 characters, confirmed)"),
     *             @OA\Property(property="new_password_confirmation", type="string", example="newpassword123", description="Confirmation of the new password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password updated successfully")
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
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="current_password",
     *                     type="array",
     *                     @OA\Items(type="string", example="The provided password is incorrect.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password update failed"),
     *             @OA\Property(property="error", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Ensure the user is authenticated
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'message' => 'User not authenticated',
                ], 401);
            }

            // Validate the request
            $request->validate([
                'current_password' => ['required', 'string'],
                'new_password' => ['required', 'string', Rules\Password::defaults(), 'confirmed'],
                'new_password_confirmation' => ['required', 'string'],
            ]);

            // Check if current password is correct
            if (!Hash::check($request->current_password, $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['The provided password is incorrect.'],
                ]);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            // Optionally revoke all tokens to force re-login (uncomment if desired)
            // $user->tokens()->delete();

            return response()->json([
                'message' => 'Password updated successfully'
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Password update failed: ' . $e->getMessage(), [
                'user_id' => $request->user() ? $request->user()->id : null,
                'request' => $request->all()
            ]);
            return response()->json([
                'message' => 'Password update failed',
                'error' => 'Internal server error'
            ], 500);
        }
    }
}