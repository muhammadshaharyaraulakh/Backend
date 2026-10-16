<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\LoginResponse; 
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Illuminate\Http\Response;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);
        // -----------------------------------------------------------------
        // 1. Custom Register Response
        // -----------------------------------------------------------------
        $this->app->singleton(RegisterResponse::class, function () {
            return new class implements RegisterResponse {
                public function toResponse($request)
                {
                    $user = $request->user();
                    $token = $user->createToken('api-token')->plainTextToken;

                    return response()->json([
                        'message' => 'Registration successful',
                        'token' => $token,
                        'user' => $user,
                    ], Response::HTTP_CREATED);
                }
            };
        });
        // -----------------------------------------------------------------
        // 2. Custom Login Response
        // -----------------------------------------------------------------
        $this->app->singleton(LoginResponse::class, function () {
            return new class implements LoginResponse {
                public function toResponse($request)
                {
                    $user = $request->user();

                    //  2FA enable 
                    if ($user->hasEnabledTwoFactorAuthentication()) {
                        return response()->json([
                            'two_factor' => true,
                            'message' => 'Please enter your 2FA OTP code.',
                            'challenge_token' => Crypt::encryptString($user->id),
                        ], Response::HTTP_OK);
                    }

                    // 2FA not Active  
                    $token = $user->createToken('api-token')->plainTextToken;

                    return response()->json([
                        'two_factor' => false,
                        'message' => 'Login successful',
                        'token' => $token,
                        'user' => $user,
                    ], Response::HTTP_OK);
                }
            };
        });
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('passkeys', function (Request $request) {
            $credentialId = $request->input('credential.id');

            return Limit::perMinute(10)->by(
                ($credentialId ?: $request->session()->getId()).'|'.$request->ip()
            );
        });
    }
}
