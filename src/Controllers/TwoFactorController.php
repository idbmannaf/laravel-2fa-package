<?php

namespace Mannaf\Laravel2FA\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use PragmaRX\Google2FAQRCode\Google2FA;
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
    }

    // Show 2FA setup page with QR code
    public function setup()
    {
        $user = Auth::user();

        // Generate secret key if not set
        if (!$user->google2fa_secret) {
            $secretKey = $this->google2fa->generateSecretKey();
            $user->google2fa_secret = $secretKey;
            $user->save();
        } else {
            $secretKey = $user->google2fa_secret;
        }

        $companyName = config('app.name');
        $qrImage = $this->google2fa->getQRCodeUrl(
            $companyName,
            $user->email,
            $secretKey
        );

        // Generate QR code SVG or PNG data URI
        $qrCode = QrCode::size(200)->generate($qrImage);

        return view('twofactor::setup', [
            'secret' => $secretKey,
            'qrCode' => $qrCode,
        ]);
    }

    // Enable 2FA after verifying user code on setup page
    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        $user = Auth::user();

        if ($this->google2fa->verifyKey($user->google2fa_secret, $request->input('code'))) {
            // 2FA enabled, store flag if needed
            $user->two_factor_enabled = true;
            $user->save();

            return redirect(config('twofactor.redirect_after'))->with('status', '2FA enabled successfully.');
        }

        return back()->withErrors(['code' => 'Invalid verification code.']);
    }

    // Show 2FA verify form on login
    public function showVerifyForm()
    {
        // User should be logged out but session with user id stored
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

        if (!$user) {
            return redirect()->route('login')->withErrors('User not found.');
        }

        if ($this->google2fa->verifyKey($user->google2fa_secret, $request->input('code'))) {
            Auth::login($user);
            Session::put('2fa_passed', true);
            Session::forget('2fa:user:id');

            return redirect()->intended(config('twofactor.redirect_after'));
        }

        return back()->withErrors(['code' => 'Invalid verification code.']);
    }

    // Disable 2FA (optional)
    public function disable(Request $request)
    {
        $user = Auth::user();
        $user->google2fa_secret = null;
        $user->two_factor_enabled = false;
        $user->save();

        return redirect(config('twofactor.redirect_after'))->with('status', '2FA disabled.');
    }
}
