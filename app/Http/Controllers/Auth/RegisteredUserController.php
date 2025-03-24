<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RegisteredUserController extends Controller
{
    /**
     * Handle user registration and return user data.
     *
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Authentication"},
     *     summary="Register a new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string", example="John Doe", description="User's full name"),
     *                 @OA\Property(property="email", type="string", format="email", example="user@example.com", description="User's email address"),
     *                 @OA\Property(property="password", type="string", example="password123", description="User's password (min 8 characters)"),
     *                 @OA\Property(property="password_confirmation", type="string", example="password123", description="Password confirmation"),
     *                 @OA\Property(property="avatar", type="string", format="binary", description="User's avatar image (optional, max 1MB, jpeg/png/jpg/gif)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User registered successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="user@example.com"),
     *                 @OA\Property(property="avatar", type="string", nullable=true, example="http://domain/storage/avatars/avatar.jpg")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed or email already registered",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="email",
     *                     type="array",
     *                     @OA\Items(type="string", example="This email address is already in use.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Registration failed"),
     *             @OA\Property(property="error", type="string", example="Internal server error"),
     *             @OA\Property(property="debug_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:1024'],
            ]);

            // Check if email already exists
            if (User::where('email', $validated['email'])->exists()) {
                throw ValidationException::withMessages([
                    'email' => ['This email address is already in use.'],
                ]);
            }

            // Handle avatar upload if present
            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                try {
                    $avatarPath = $request->file('avatar')->store('avatars', 'public');
                } catch (\Exception $e) {
                    Log::error('Avatar upload failed: ' . $e->getMessage(), ['request' => $request->all()]);
                    throw new HttpException(500, 'Avatar upload failed: File storage error');
                }
            }

            // Create the new user
            $user = User::create([
                'id' => Str::uuid(),
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'avatar' => $avatarPath,
            ]);

            // Dispatch registered event and log in the user
            try {
                event(new Registered($user));
                Auth::login($user);
            } catch (\Exception $e) {
                Log::warning('Post-registration process failed: ' . $e->getMessage(), ['user_id' => $user->id]);
                // Continue even if event or login fails, as user is already created
            }

            // Optionally generate a Sanctum token (uncomment if needed)
            // $token = $user->createToken('auth_token')->plainTextToken;

            // Return success response
            return response()->json([
                'message' => 'User registered successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $avatarPath ? asset('storage/' . $avatarPath) : null,
                    // 'token' => $token, // Uncomment if token is generated
                    // 'token_type' => 'Bearer', // Uncomment if token is generated
                ]
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (QueryException $e) {
            Log::error('Database error during user creation: ' . $e->getMessage(), ['request' => $request->all()]);
            return response()->json([
                'message' => 'Registration failed',
                'error' => 'Database operation failed'
            ], 500);

        } catch (HttpException $e) {
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], $e->getStatusCode());

        } catch (\Exception $e) {
            $debugId = Str::uuid();
            Log::error('Unexpected error during registration: ' . $e->getMessage(), [
                'request' => $request->all(),
                'debug_id' => $debugId
            ]);
            return response()->json([
                'message' => 'Registration failed',
                'error' => 'Internal server error',
                'debug_id' => $debugId
            ], 500);
        }
    }
}