<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicUser;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * API Controller for Public Users
 * Handles authentication and user management for the public portal
 */
class PublicUserApiController extends Controller
{
    // Message constants
    private const VALIDATION_ERROR_MESSAGE = 'Validation error';
    private const INVALID_CREDENTIALS = 'Invalid credentials';
    private const ACCOUNT_NOT_APPROVED = 'Account not approved yet';
    private const TOKEN_VALID = 'Token is valid';

    // Validation rule constants
    private const REQUIRED_STRING = 'required|string';
    private const REQUIRED_STRING_MAX_255 = 'required|string|max:255';
    private const NULLABLE_STRING_MAX_20 = 'nullable|string|max:20';
    private const NULLABLE_STRING_MAX_255 = 'nullable|string|max:255';
    private const REQUIRED_EMAIL = 'required|email';

    private const LOGIN_RULES = [
        'email' => self::REQUIRED_EMAIL,
        'password' => self::REQUIRED_STRING,
    ];

    private const REGISTER_RULES = [
        'name' => self::REQUIRED_STRING_MAX_255,
        'first_name' => self::REQUIRED_STRING_MAX_255,
        'email' => 'required|string|email|max:255|unique:public_users',
        'password' => 'required|string|min:8|confirmed',
        'phone1' => self::NULLABLE_STRING_MAX_20,
        'address' => self::NULLABLE_STRING_MAX_255,
    ];

    private const UPDATE_PROFILE_RULES = [
        'name' => 'sometimes|required|string|max:255',
        'first_name' => 'sometimes|required|string|max:255',
        'phone1' => self::NULLABLE_STRING_MAX_20,
        'phone2' => self::NULLABLE_STRING_MAX_20,
        'address' => self::NULLABLE_STRING_MAX_255,
        'email' => 'sometimes|required|string|email|max:255',
    ];

    /**
     * API: Login user
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate(self::LOGIN_RULES);
        $user = PublicUser::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return $this->errorResponse(self::INVALID_CREDENTIALS, 401);
        }

        if (!$user->is_approved) {
            return $this->errorResponse(self::ACCOUNT_NOT_APPROVED, 403);
        }

        $token = $user->createToken('shelve-app')->plainTextToken;

        return $this->successResponse('Login successful', [
            'user' => $this->transformUser($user),
            'token' => $token
        ]);
    }

    /**
     * API: Register new user
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate(self::REGISTER_RULES);

        $userData = $this->prepareUserData($validated);
        $user = PublicUser::create($userData);

        return $this->successResponse(
            'Registration successful. Your account is pending approval.',
            $this->transformUser($user),
            201
        );
    }

    /**
     * API: Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return $this->successResponse('Logout successful');
    }

    /**
     * API: Verify token
     */
    public function verifyToken(Request $request): JsonResponse
    {
        $request->validate(['token' => self::REQUIRED_STRING]);

        return $this->successResponse(self::TOKEN_VALID, [
            'user' => $this->transformUser($request->user())
        ]);
    }

    /**
     * API: Forgot password
     * Note: This is a placeholder implementation
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate(['email' => self::REQUIRED_EMAIL]);
        $user = PublicUser::where('email', $validated['email'])->first();

        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }

        // Placeholder: In production, implement actual password reset logic
        return $this->successResponse('Password reset instructions sent to your email');
    }

    /**
     * API: Reset password
     * Note: This is a placeholder implementation
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => self::REQUIRED_STRING,
            'password' => 'required|string|min:8|confirmed'
        ]);

        // Placeholder: In production, implement actual password reset logic
        return $this->successResponse('Password reset successful');
    }

    /**
     * API: Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $rules = self::UPDATE_PROFILE_RULES;
        $rules['email'] = 'sometimes|required|string|email|max:255|unique:public_users,email,' . $user->id;

        $validated = $request->validate($rules);
        $user->update($validated);

        return $this->successResponse('Profile updated successfully', $this->transformUser($user->fresh()));
    }

    /**
     * Prepare user data for creation
     */
    private function prepareUserData(array $validated): array
    {
        $validated['password'] = Hash::make($validated['password']);
        $validated['is_approved'] = false;
        $validated['phone1'] = $validated['phone1'] ?? 'Non renseigné';
        $validated['phone2'] = 'Non renseigné';
        $validated['address'] = $validated['address'] ?? 'Non renseignée';

        return $validated;
    }

    /**
     * Transform user data for API response
     */
    private function transformUser($user): array
    {
        if (!$user) {
            return [];
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'first_name' => $user->first_name,
            'full_name' => trim($user->first_name . ' ' . $user->name),
            'email' => $user->email,
            'phone1' => $user->phone1,
            'phone2' => $user->phone2,
            'address' => $user->address,
            'is_approved' => $user->is_approved,
            'created_at' => $user->created_at?->toISOString(),
            'updated_at' => $user->updated_at?->toISOString(),
        ];
    }

    /**
     * Success response helper
     */
    private function successResponse(string $message, $data = null, int $status = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    /**
     * Error response helper
     */
    private function errorResponse(string $message, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $status);
    }
}
