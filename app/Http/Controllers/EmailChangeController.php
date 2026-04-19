<?php

namespace App\Http\Controllers;

use App\Models\EmailChangeVerification;
use App\Notifications\EmailChangeVerification as EmailChangeVerificationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EmailChangeController extends Controller
{
    /**
     * Send email change verification
     */
    public function sendVerification(Request $request)
    {
        $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . $request->user()->id,
            ],
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already in use.',
        ]);

        // Delete any existing verification for this user
        EmailChangeVerification::where('user_id', $request->user()->id)->delete();

        // Create new verification token
        $token = Str::random(64);
        
        EmailChangeVerification::create([
            'user_id' => $request->user()->id,
            'new_email' => $request->email,
            'token' => $token,
            'expires_at' => Carbon::now()->addMinutes(60),
        ]);

        // [COMMENTED OUT - EMAIL FUNCTION DISABLED] - Email verification disabled for LAN-only system
        // Send verification email directly to the NEW email address
        /*
        Notification::route('mail', $request->email)
            ->notify(new EmailChangeVerificationNotification($token, $request->email, $request->user()->name));
        */

        return back()->with('status', 'verification-link-sent');
    }

    /**
     * Verify the email change
     */
    public function verify(Request $request)
    {
        $verification = EmailChangeVerification::where('token', $request->token)
            ->where('new_email', $request->email)
            ->first();

        if (!$verification) {
            return redirect()->route('login')->with('error', 'Invalid verification link.');
        }

        if ($verification->isExpired()) {
            $verification->delete();
            return redirect()->route('login')->with('error', 'Verification link has expired.');
        }

        // Check if email is still unique
        if (DB::table('users')->where('email', $verification->new_email)->exists()) {
            $verification->delete();
            return redirect()->route('login')->with('error', 'This email address is already in use.');
        }

        // Update user email
        $user = $verification->user;
        $user->email = $verification->new_email;
        $user->email_verified_at = now();
        $user->save();

        // Delete verification record
        $verification->delete();

        // Log out the user from all devices
        \Illuminate\Support\Facades\Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'email-updated-login');
    }
}
