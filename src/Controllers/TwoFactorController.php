<?php

namespace mannaf\Laravel2FA\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use PragmaRX\Google2FA\Google2FA;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;

class TwoFactorController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
        $this->google2fa->setWindow(config('twofactor.google2fa.window', 4));
    }

    // Show 2FA setup page with QR code
    public function setup()
    {
        $user = Auth::user();
        $secretField = config('twofactor.user_model.secret_field', 'two_factor_secret');
        $enabledField = config('twofactor.user_model.enabled_field', 'two_factor_enabled');

        // Generate secret key if not set
        if (!$user->$secretField) {
            $secretKey = $this->google2fa->generateSecretKey();
            $user->$secretField = $secretKey;
            $user->save();
        } else {
            $secretKey = $user->$secretField;
        }

        $companyName = config('app.name');
        $qrImage = $this->google2fa->getQRCodeUrl(
            $companyName,
            $user->email,
            $secretKey
        );

        // Generate QR code SVG or PNG data URI
        $qrCode = QrCode::size(config('twofactor.qr_code.size', 200))
            ->format(config('twofactor.qr_code.format', 'png'))
            ->generate($qrImage);

        return view('twofactor::setup', [
            'secret' => $secretKey,
            'qrCode' => $qrCode,
            'user' => $user,
        ]);
    }

    // Enable 2FA after verifying user code on setup page
    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        $user = Auth::user();
        $secretField = config('twofactor.user_model.secret_field', 'two_factor_secret');
        $enabledField = config('twofactor.user_model.enabled_field', 'two_factor_enabled');

        if ($this->google2fa->verifyKey($user->$secretField, $request->input('code'))) {
            // 2FA enabled
            $user->$enabledField = true;
            $user->save();

            // Set session flag
            Session::put(config('twofactor.session.key', '2fa_verified'), true);

            return redirect(config('twofactor.redirect_after_setup', '/home'))
                ->with('status', 'Two-factor authentication enabled successfully.');
        }

        return back()->withErrors(['code' => 'Invalid verification code. Please try again.']);
    }

    // Show 2FA verify form on login
    public function showVerifyForm()
    {
        // Check if user session exists
        if (!Session::has('2fa:user:id')) {
            return redirect()->route('login')->withErrors('Session expired, please login again.');
        }

        return view('twofactor::verify');
    }

    // Verify 2FA code on login
    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $userId = Session::get('2fa:user:id');

        if (!$userId) {
            return redirect()->route('login')->withErrors('Session expired, please login again.');
        }

        $userModel = config('auth.providers.users.model');
        $user = (new $userModel)->find($userId);
        $secretField = config('twofactor.user_model.secret_field', 'two_factor_secret');

        if (!$user || !$user->$secretField) {
            return redirect()->route('login')->withErrors('User not found or 2FA not configured.');
        }

        if ($this->google2fa->verifyKey($user->$secretField, $request->input('code'))) {
            Auth::login($user);

            // Set session flags
            Session::put(config('twofactor.session.key', '2fa_verified'), true);
            Session::forget('2fa:user:id');

            return redirect()->intended(config('twofactor.redirect_after_verify', '/home'))
                ->with('status', 'Two-factor authentication verified successfully.');
        }

        return back()->withErrors(['code' => 'Invalid verification code. Please try again.']);
    }

    // Disable 2FA (optional)
    public function disable(Request $request)
    {
        $user = Auth::user();
        $secretField = config('twofactor.user_model.secret_field', 'two_factor_secret');
        $enabledField = config('twofactor.user_model.enabled_field', 'two_factor_enabled');

        $user->$secretField = null;
        $user->$enabledField = false;
        $user->save();

        // Clear session flags
        Session::forget(config('twofactor.session.key', '2fa_verified'));

        return redirect(config('twofactor.redirect_after_setup', '/home'))
            ->with('status', 'Two-factor authentication disabled successfully.');
    }
}
