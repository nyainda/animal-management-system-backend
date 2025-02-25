<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'Email already verified',
                    'verified' => true
                ], 200);
            }

            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }

            return response()->json([
                'message' => 'Email verified successfully',
                'verified' => true
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Email verification failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
