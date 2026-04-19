<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     * [COMMENTED OUT - EMAIL VERIFICATION DISABLED] - Email verification not needed for LAN-only system
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        // Email verification is disabled, redirect to dashboard
        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        
        // if ($request->user()->hasVerifiedEmail()) {
        //     return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        // }

        // if ($request->user()->markEmailAsVerified()) {
        //     event(new Verified($request->user()));
        // }

        // return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
