<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => ['required', 'email'],
            ]);

            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'message' => 'Password reset link sent successfully',
                    'status' => __($status)
                ], 200);
            }

            return response()->json([
                'message' => 'Failed to send reset link',
                'errors' => [
                    'email' => [__($status)]
                ]
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to process request',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
