# Laravel 2FA Package

A comprehensive Laravel package for implementing Two-Factor Authentication using Google Authenticator.

## Features

- 🔐 Easy 2FA setup and verification
- 📱 QR code generation for authenticator apps
- 🛡️ Middleware for protecting routes
- ⚙️ Highly configurable
- 🎨 Bootstrap-styled views
- 📦 Laravel package structure

## Installation

1. **Install the package via Composer:**

```bash
composer require laravel-2fa/package
```

2. **Publish the configuration file:**

```bash
php artisan vendor:publish --tag=twofactor-config
```

3. **Publish the views (optional):**

```bash
php artisan vendor:publish --tag=twofactor-views
```

4. **Add the required database columns to your users table:**

Create a migration to add the necessary columns:

```bash
php artisan make:migration add_two_factor_to_users_table
```

Add the following to your migration:

```php
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
```

5. **Run the migration:**

```bash
php artisan migrate
```

## Usage

### Basic Setup

1. **Add the middleware to your routes:**

```php
Route::middleware(['auth', '2fa'])->group(function () {
    // Your protected routes here
});
```

2. **Add the middleware to your User model:**

```php
use Laravel2FA\Middleware\TwoFactorMiddleware;

class User extends Authenticatable
{
    // Your existing code...
}
```

### Routes

The package automatically registers the following routes:

- `GET /2fa/setup` - Show 2FA setup page
- `POST /2fa/enable` - Enable 2FA for user
- `GET /2fa/verify` - Show 2FA verification page
- `POST /2fa/verify` - Verify 2FA code
- `POST /2fa/disable` - Disable 2FA

### Views

The package includes two main views:

- `twofactor::setup` - For setting up 2FA
- `twofactor::verify` - For verifying 2FA codes

### Configuration

You can customize the package behavior by modifying the `config/twofactor.php` file:

```php
return [
    'redirect_after_setup' => '/dashboard',
    'redirect_after_verify' => '/dashboard',
    'qr_code' => [
        'size' => 200,
        'format' => 'png',
    ],
    'google2fa' => [
        'window' => 4,
        'length' => 6,
        'time_step' => 30,
    ],
    // ... more options
];
```

## API Reference

### TwoFactorController

#### Methods

- `showSetup()` - Display the 2FA setup page
- `enable(Request $request)` - Enable 2FA for the authenticated user
- `showVerify()` - Display the 2FA verification page
- `verify(Request $request)` - Verify the 2FA code
- `disable(Request $request)` - Disable 2FA for the authenticated user

### TwoFactorMiddleware

The middleware automatically checks if 2FA is required and redirects users accordingly.

## Security Considerations

- 2FA secrets are hashed before storage
- Session-based verification to prevent repeated prompts
- Configurable time windows for clock skew tolerance
- Automatic redirection to setup if 2FA is not configured

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Support

If you encounter any issues or have questions, please open an issue on GitHub.
