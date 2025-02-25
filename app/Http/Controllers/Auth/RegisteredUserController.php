<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log; // Added for logging
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException; // Added for database errors
use Symfony\Component\HttpKernel\Exception\HttpException; // Added for HTTP exceptions

class RegisteredUserController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255'],
                'password' => ['required', 'confirmed', 'min:8'],
                'avatar' => ['nullable', 'image', 'max:1024'],
            ]);

            // Check if email already exists
            if (User::where('email', $validated['email'])->exists()) {
                return response()->json([
                    'message' => 'Email already registered',
                    'errors' => [
                        'email' => ['This email address is already in use.']
                    ]
                ], 422);
            }

            // Initialize avatar path as null
            $avatarPath = null;

            // Handle avatar upload if present
            if ($request->hasFile('avatar')) {
                try {
                    $avatarPath = $request->file('avatar')->store('avatars', 'public');
                } catch (\Exception $e) {
                    Log::error('Avatar upload failed: ' . $e->getMessage());
                    return response()->json([
                        'message' => 'Avatar upload failed',
                        'error' => 'File storage error'
                    ], 500);
                }
            }

            // Create the new user
            try {
                $user = User::create([
                    'id' => Str::uuid(),
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'avatar' => $avatarPath,
                ]);
            } catch (QueryException $e) {
                Log::error('Database error during user creation: ' . $e->getMessage());
                return response()->json([
                    'message' => 'User creation failed',
                    'error' => 'Database operation error'
                ], 500);
            }

            // Dispatch registered event with error handling
            try {
                event(new Registered($user));
                Auth::login($user);
            } catch (\Exception $e) {
                Log::error('Post-registration process failed: ' . $e->getMessage());
                // Continue response even if post-registration actions fail
            }

            // Return success response with user data
            return response()->json([
                'message' => 'User registered successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $avatarPath ? asset('storage/' . $avatarPath) : null,
                ]
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (QueryException $e) {
            Log::error('Database error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Database operation failed',
                'error' => 'Could not complete database operation'
            ], 500);

        } catch (HttpException $e) {
            // Preserve existing HTTP status code
            return response()->json([
                'message' => 'HTTP error occurred',
                'error' => $e->getMessage()
            ], $e->getStatusCode());

        } catch (\Exception $e) {
            Log::error('Unexpected error during registration: ' . $e->getMessage());
            return response()->json([
                'message' => 'Registration failed',
                'error' => 'Internal server error', // Generic message for clients
                'debug_id' => Str::uuid() // Unique ID for tracking in logs
            ], 500);
        }
    }
}
