# Laravel 2FA Package

A comprehensive Laravel package for implementing Two-Factor Authentication using Google Authenticator. This package provides a complete 2FA solution with QR code generation, middleware protection, and beautiful Bootstrap-styled interfaces.

## Features

- 🔐 **Easy 2FA Setup**: Simple setup process with QR code generation
- 📱 **QR Code Support**: Automatic QR code generation for authenticator apps
- 🛡️ **Middleware Protection**: Route protection with configurable middleware
- ⚙️ **Highly Configurable**: Extensive configuration options
- 🎨 **Modern UI**: Bootstrap-styled responsive views
- 🔒 **Session Management**: Secure session handling for 2FA verification
- 📦 **Laravel Package**: Proper Laravel package structure with service provider
- 🚀 **Laravel 7-12 Support**: Compatible with Laravel 7 through 12

## Requirements

- PHP >= 7.3
- Laravel >= 7.0
- Google Authenticator app (or compatible TOTP app)

## Installation

### 1. Install the Package

```bash
composer require mannaf/laravel-2fa
```

### 2. Publish Configuration

```bash
php artisan vendor:publish --tag=twofactor-config
```

### 3. Publish Views (Optional)

```bash
php artisan vendor:publish --tag=twofactor-views
```

### 4. Database Migration

Create a migration to add the required columns to your users table:

```bash
php artisan make:migration add_two_factor_to_users_table
```

Add the following to your migration:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTwoFactorToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('two_factor_secret')->nullable();
            $table->boolean('two_factor_enabled')->default(false);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['two_factor_secret', 'two_factor_enabled']);
        });
    }
}
```

### 5. Run Migration

```bash
php artisan migrate
```

## Configuration

The package configuration file is located at `config/twofactor.php`. Here are the main configuration options:

```php
return [
    // Enable/disable 2FA globally
    'enabled' => env('ENABLE_2FA', true),

    // Redirect URLs after actions
    'redirect_after_setup' => env('2FA_REDIRECT_AFTER_SETUP', '/home'),
    'redirect_after_verify' => env('2FA_REDIRECT_AFTER_VERIFY', '/home'),

    // QR Code settings
    'qr_code' => [
        'size' => env('2FA_QR_SIZE', 200),
        'format' => env('2FA_QR_FORMAT', 'png'),
    ],

    // Google2FA settings
    'google2fa' => [
        'window' => env('2FA_WINDOW', 4),      // Clock skew tolerance
        'length' => env('2FA_LENGTH', 6),      // Code length
        'time_step' => env('2FA_TIME_STEP', 30), // Time step in seconds
    ],

    // Session settings
    'session' => [
        'key' => env('2FA_SESSION_KEY', '2fa_verified'),
        'lifetime' => env('2FA_SESSION_LIFETIME', 120), // Minutes
    ],

    // User model field names
    'user_model' => [
        'secret_field' => env('2FA_SECRET_FIELD', 'two_factor_secret'),
        'enabled_field' => env('2FA_ENABLED_FIELD', 'two_factor_enabled'),
    ],

    // Middleware exceptions
    'middleware' => [
        'except' => [
            '2fa.*',
            'login',
            'logout',
            'password.*',
        ],
    ],
];
```

## Usage

### Basic Setup

#### 1. Add Middleware to Routes

Protect your routes with the 2FA middleware:

```php
// In routes/web.php
Route::middleware(['auth', '2fa'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/profile', [ProfileController::class, 'index']);
    // Your protected routes here
});
```

#### 2. Add Setup Route

Add a route for users to set up 2FA:

```php
Route::middleware(['auth'])->group(function () {
    Route::get('/2fa/setup', [TwoFactorController::class, 'setup'])->name('twofactor.setup');
    Route::post('/2fa/enable', [TwoFactorController::class, 'enable'])->name('twofactor.enable');
    Route::post('/2fa/disable', [TwoFactorController::class, 'disable'])->name('twofactor.disable');
});
```

### Available Routes

The package automatically registers these routes:

- `GET /2fa/setup` - Show 2FA setup page
- `POST /2fa/enable` - Enable 2FA for user
- `GET /2fa/verify` - Show 2FA verification page
- `POST /2fa/verify` - Verify 2FA code
- `POST /2fa/disable` - Disable 2FA

### User Experience Flow

1. **User logs in** with email/password
2. **If 2FA is enabled**, user is redirected to verification page
3. **User enters 6-digit code** from authenticator app
4. **Upon successful verification**, user is redirected to intended page
5. **Session is marked** as 2FA verified for subsequent requests

### Setting Up 2FA for Users

Users can set up 2FA by visiting `/2fa/setup`. The process includes:

1. **QR Code Generation**: Automatic QR code for easy setup
2. **Manual Entry**: Secret key provided for manual entry
3. **Verification**: User enters code from authenticator app
4. **Confirmation**: 2FA is enabled upon successful verification

## API Reference

### TwoFactorController

#### Methods

- `setup()` - Display the 2FA setup page with QR code
- `enable(Request $request)` - Enable 2FA after code verification
- `showVerifyForm()` - Display the 2FA verification page
- `verify(Request $request)` - Verify the 2FA code and log user in
- `disable(Request $request)` - Disable 2FA for the authenticated user

### TwoFactorMiddleware

The middleware automatically:

- Checks if 2FA is globally enabled
- Verifies if user has 2FA enabled
- Redirects to verification if needed
- Manages session state

## Security Features

- **Secret Storage**: 2FA secrets are stored securely in the database
- **Session Management**: Proper session handling prevents repeated prompts
- **Clock Skew Tolerance**: Configurable time window for clock differences
- **Secure Redirects**: Proper redirect handling after verification
- **CSRF Protection**: All forms include CSRF protection

## Environment Variables

You can configure the package using these environment variables:

```env
# Enable/disable 2FA globally
ENABLE_2FA=true

# Redirect URLs
2FA_REDIRECT_AFTER_SETUP=/dashboard
2FA_REDIRECT_AFTER_VERIFY=/dashboard

# QR Code settings
2FA_QR_SIZE=200
2FA_QR_FORMAT=png

# Google2FA settings
2FA_WINDOW=4
2FA_LENGTH=6
2FA_TIME_STEP=30

# Session settings
2FA_SESSION_KEY=2fa_verified
2FA_SESSION_LIFETIME=120

# User model field names
2FA_SECRET_FIELD=two_factor_secret
2FA_ENABLED_FIELD=two_factor_enabled
```

## Testing

### Manual Testing

1. **Install the package** in a Laravel application
2. **Run migrations** to add required database columns
3. **Configure the package** using environment variables
4. **Set up 2FA** for a test user
5. **Test the flow**:
   - Login with email/password
   - Verify 2FA code
   - Access protected routes
   - Test session persistence

### Automated Testing

The package includes comprehensive tests. Run them with:

```bash
composer test
```

## Troubleshooting

### Common Issues

1. **QR Code Not Displaying**: Ensure the QR code package is properly installed
2. **Invalid Code Errors**: Check clock synchronization between server and authenticator app
3. **Session Issues**: Verify session configuration and middleware order
4. **Route Not Found**: Ensure routes are properly registered in the service provider

### Debug Mode

Enable debug mode to see detailed error messages:

```php
// In config/twofactor.php
'debug' => env('APP_DEBUG', false),
```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Support

- **GitHub Issues**: [Report bugs or request features](https://github.com/mannaf/laravel-2fa/issues)
- **Documentation**: Check the [Wiki](https://github.com/mannaf/laravel-2fa/wiki) for detailed guides
- **Discussions**: Join the [GitHub Discussions](https://github.com/mannaf/laravel-2fa/discussions)

## Changelog

### v1.0.0

- Initial release
- Basic 2FA functionality
- QR code generation
- Middleware protection
- Bootstrap-styled views

---

**Made with ❤️ by Abdul Mannaf**
