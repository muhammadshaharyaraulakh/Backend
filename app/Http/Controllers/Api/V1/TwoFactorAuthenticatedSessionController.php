<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;

class TwoFactorAuthenticatedSessionController extends Controller
{
    /**
     * Handle the 2FA challenge and issue Sanctum token.
     */
    public function store(Request $request, TwoFactorAuthenticationProvider $provider): JsonResponse
    {
        $request->validate([
            'challenge_token' => ['required', 'string'],
            'code' => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
        ]);

        try {
            $userId = Crypt::decryptString($request->challenge_token);
            $user = User::findOrFail($userId);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Invalid or expired challenge token.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($request->filled('code')) {
            if (! $provider->verify(decrypt($user->two_factor_secret), $request->code)) {
                return response()->json([
                    'message' => 'The provided two factor authentication code was invalid.'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        } 
        elseif ($request->filled('recovery_code')) {
            $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);

            if (! in_array($request->recovery_code, $recoveryCodes)) {
                return response()->json([
                    'message' => 'The provided recovery code was invalid.'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $user->replaceRecoveryCode($request->recovery_code);
        } else {
            return response()->json([
                'message' => 'Code or recovery code is required.'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => '2FA verified. Login successful.',
            'token' => $token,
            'user' => $user,
        ], Response::HTTP_OK);
    }
}