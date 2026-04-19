<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

// ============================================================================
// EMAIL VERIFICATION ROUTE
// ============================================================================
// [COMMENTED OUT - EMAIL VERIFICATION DISABLED] - Email verification not needed for LAN-only system
// ============================================================================
/*
Route::get('verify-email/{id}/{hash}', function (Request $request) {
    // Find the user by ID from the URL
    $user = \App\Models\User::findOrFail($request->route('id'));
    
    // Verify the hash is valid (security check)
    if (! hash_equals(
        (string) $request->route('hash'),
        sha1($user->getEmailForVerification())
    )) {
        abort(403, 'Invalid verification link');
    }
    
    // Check if email is already verified
    if ($user->hasVerifiedEmail()) {
        // Log out if logged in
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
        return redirect()->route('login')->with('status', 'Email already verified. Please sign in.');
    }
    
    // Mark email as verified in database
    $user->markEmailAsVerified();
    
    // IMPORTANT: Log out the user if they're logged in
    if (Auth::check()) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
    
    // Redirect to login page with success message
    return redirect()->route('login')->with('status', 'Your email has been verified! Please sign in to continue.');
})->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');
*/