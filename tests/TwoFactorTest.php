<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Enable 2FA for testing
        config(['twofactor.enabled' => true]);
    }

    /** @test */
    public function it_can_show_2fa_setup_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/2fa/setup');

        $response->assertStatus(200);
        $response->assertViewIs('twofactor::setup');
        $response->assertSee('Setup Two-Factor Authentication');
    }

    /** @test */
    public function it_generates_secret_key_on_setup()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/2fa/setup');

        $response->assertStatus(200);

        // Check that user now has a secret key
        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);
        $this->assertIsString($user->two_factor_secret);
    }

    /** @test */
    public function it_can_enable_2fa_with_valid_code()
    {
        $user = User::factory()->create();
        $google2fa = new Google2FA();

        // Generate secret and get current code
        $secret = $google2fa->generateSecretKey();
        $user->two_factor_secret = $secret;
        $user->save();

        $code = $google2fa->getCurrentOtp($secret);

        $response = $this->actingAs($user)
            ->post('/2fa/enable', ['code' => $code]);

        $response->assertRedirect();

        $user->refresh();
        $this->assertTrue($user->two_factor_enabled);
    }

    /** @test */
    public function it_rejects_invalid_2fa_code()
    {
        $user = User::factory()->create();
        $google2fa = new Google2FA();

        // Generate secret
        $secret = $google2fa->generateSecretKey();
        $user->two_factor_secret = $secret;
        $user->save();

        $response = $this->actingAs($user)
            ->post('/2fa/enable', ['code' => '123456']);

        $response->assertSessionHasErrors('code');

        $user->refresh();
        $this->assertFalse($user->two_factor_enabled);
    }

    /** @test */
    public function it_can_show_verification_page()
    {
        $user = User::factory()->create();
        Session::put('2fa:user:id', $user->id);

        $response = $this->get('/2fa/verify');

        $response->assertStatus(200);
        $response->assertViewIs('twofactor::verify');
        $response->assertSee('Two-Factor Authentication');
    }

    /** @test */
    public function it_can_verify_2fa_code_and_login()
    {
        $user = User::factory()->create();
        $google2fa = new Google2FA();

        // Setup 2FA for user
        $secret = $google2fa->generateSecretKey();
        $user->two_factor_secret = $secret;
        $user->two_factor_enabled = true;
        $user->save();

        // Store user ID in session (simulating login flow)
        Session::put('2fa:user:id', $user->id);

        $code = $google2fa->getCurrentOtp($secret);

        $response = $this->post('/2fa/verify', ['code' => $code]);

        $response->assertRedirect();
        $this->assertAuthenticated();
        $this->assertTrue(Session::has('2fa_verified'));
    }

    /** @test */
    public function it_rejects_invalid_verification_code()
    {
        $user = User::factory()->create();
        Session::put('2fa:user:id', $user->id);

        $response = $this->post('/2fa/verify', ['code' => '123456']);

        $response->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    /** @test */
    public function it_can_disable_2fa()
    {
        $user = User::factory()->create();
        $user->two_factor_secret = 'test_secret';
        $user->two_factor_enabled = true;
        $user->save();

        $response = $this->actingAs($user)
            ->post('/2fa/disable');

        $response->assertRedirect();

        $user->refresh();
        $this->assertNull($user->two_factor_secret);
        $this->assertFalse($user->two_factor_enabled);
    }

    /** @test */
    public function middleware_redirects_to_verification_when_2fa_required()
    {
        $user = User::factory()->create();
        $user->two_factor_secret = 'test_secret';
        $user->two_factor_enabled = true;
        $user->save();

        // Create a protected route
        $this->app['router']->get('/protected', function () {
            return 'Protected content';
        })->middleware(['auth', '2fa']);

        $response = $this->actingAs($user)
            ->get('/protected');

        $response->assertRedirect('/2fa/verify');
    }

    /** @test */
    public function middleware_allows_access_when_2fa_verified()
    {
        $user = User::factory()->create();
        $user->two_factor_secret = 'test_secret';
        $user->two_factor_enabled = true;
        $user->save();

        // Create a protected route
        $this->app['router']->get('/protected', function () {
            return 'Protected content';
        })->middleware(['auth', '2fa']);

        // Set 2FA as verified in session
        Session::put('2fa_verified', true);

        $response = $this->actingAs($user)
            ->get('/protected');

        $response->assertStatus(200);
        $response->assertSee('Protected content');
    }

    /** @test */
    public function middleware_skips_2fa_when_disabled_globally()
    {
        config(['twofactor.enabled' => false]);

        $user = User::factory()->create();
        $user->two_factor_secret = 'test_secret';
        $user->two_factor_enabled = true;
        $user->save();

        // Create a protected route
        $this->app['router']->get('/protected', function () {
            return 'Protected content';
        })->middleware(['auth', '2fa']);

        $response = $this->actingAs($user)
            ->get('/protected');

        $response->assertStatus(200);
        $response->assertSee('Protected content');
    }

    /** @test */
    public function middleware_skips_2fa_when_user_not_enabled()
    {
        $user = User::factory()->create();
        $user->two_factor_secret = null;
        $user->two_factor_enabled = false;
        $user->save();

        // Create a protected route
        $this->app['router']->get('/protected', function () {
            return 'Protected content';
        })->middleware(['auth', '2fa']);

        $response = $this->actingAs($user)
            ->get('/protected');

        $response->assertStatus(200);
        $response->assertSee('Protected content');
    }
}
