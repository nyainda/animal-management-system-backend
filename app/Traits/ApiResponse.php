<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

trait ApiResponse
{
    protected function successResponse($data, string $message, int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
            'code' => $code
        ], $code);
    }

    protected function errorResponse(string $message, int $code = 500, array $errors = []): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'code' => $code
        ];

        if (config('app.debug') && !empty($errors)) {
            $response['errors'] = $errors;
        }

        Log::error("API Error: {$message}", [
            'code' => $code,
            'errors' => $errors,
            'url' => request()?->fullUrl()
        ]);

        return response()->json($response, $code);
    }
}
