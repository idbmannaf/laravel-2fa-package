# Laravel 2FA Package - Example Setup Guide

This guide will help you set up and test the Laravel 2FA package in a fresh Laravel application.

## Prerequisites

- PHP >= 7.3
- Composer
- Laravel >= 7.0
- Database (MySQL, PostgreSQL, SQLite, etc.)

## Step 1: Create a New Laravel Application

```bash
composer create-project laravel/laravel laravel-2fa-test
cd laravel-2fa-test
```

## Step 2: Install the 2FA Package

```bash
composer require mannaf/laravel-2fa
```

## Step 3: Publish Configuration and Views

```bash
php artisan vendor:publish --tag=twofactor-config
php artisan vendor:publish --tag=twofactor-views
```

## Step 4: Create Database Migration

```bash
php artisan make:migration add_two_factor_to_users_table
```

Edit the migration file (`database/migrations/xxxx_add_two_factor_to_users_table.php`):

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

## Step 5: Run Migration

```bash
php artisan migrate
```

## Step 6: Configure Environment

Add these to your `.env` file:

```env
ENABLE_2FA=true
2FA_REDIRECT_AFTER_SETUP=/dashboard
2FA_REDIRECT_AFTER_VERIFY=/dashboard
```

## Step 7: Set Up Authentication (if not using Laravel Breeze/Jetstream)

### Option A: Using Laravel Breeze (Recommended)

```bash
composer require laravel/breeze --dev
php artisan breeze:install
npm install && npm run dev
```

### Option B: Manual Authentication Setup

Create basic login/register routes in `routes/web.php`:

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Protected Routes with 2FA
Route::middleware(['auth', '2fa'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
});

// 2FA Setup Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/2fa/setup', [TwoFactorController::class, 'setup'])->name('twofactor.setup');
    Route::post('/2fa/enable', [TwoFactorController::class, 'enable'])->name('twofactor.enable');
    Route::post('/2fa/disable', [TwoFactorController::class, 'disable'])->name('twofactor.disable');
});

// Public home route
Route::get('/', function () {
    return view('welcome');
});
```

## Step 8: Create Controllers

### Login Controller (`app/Http/Controllers/Auth/LoginController.php`)

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Check if user has 2FA enabled
            $user = Auth::user();
            if ($user->two_factor_secret && $user->two_factor_enabled) {
                // Store user ID in session and logout for 2FA verification
                session(['2fa:user:id' => $user->id]);
                Auth::logout();
                return redirect()->route('twofactor.verify');
            }

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
```

### Dashboard Controller (`app/Http/Controllers/DashboardController.php`)

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }

    public function profile()
    {
        return view('profile');
    }
}
```

## Step 9: Create Views

### Login View (`resources/views/auth/login.blade.php`)

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Login</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
```

### Dashboard View (`resources/views/dashboard.blade.php`)

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Laravel 2FA Test</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="{{ route('twofactor.setup') }}">Setup 2FA</a>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-link nav-link">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Dashboard</h4>
                    </div>
                    <div class="card-body">
                        <h5>Welcome, {{ Auth::user()->name }}!</h5>
                        <p>You are successfully logged in with 2FA protection.</p>

                        @if(Auth::user()->two_factor_enabled)
                            <div class="alert alert-success">
                                ✅ Two-factor authentication is enabled for your account.
                            </div>
                        @else
                            <div class="alert alert-warning">
                                ⚠️ Two-factor authentication is not enabled.
                                <a href="{{ route('twofactor.setup') }}" class="btn btn-sm btn-primary">Setup 2FA</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
```

## Step 10: Test the Package

### 1. Start the Development Server

```bash
php artisan serve
```

### 2. Create a Test User

Visit `http://localhost:8000/register` and create a test user.

### 3. Test 2FA Setup

1. Login with your test user
2. Visit `http://localhost:8000/2fa/setup`
3. Scan the QR code with Google Authenticator
4. Enter the 6-digit code to enable 2FA

### 4. Test 2FA Verification

1. Logout and login again
2. You should be redirected to the 2FA verification page
3. Enter the code from your authenticator app
4. You should be redirected to the dashboard

### 5. Test Protected Routes

1. Try accessing `/dashboard` directly
2. If 2FA is enabled, you should be redirected to verification
3. After verification, you should be able to access the dashboard

## Troubleshooting

### Common Issues

1. **Routes not found**: Make sure the service provider is properly registered
2. **Database errors**: Check that migrations ran successfully
3. **QR code not displaying**: Ensure the QR code package is installed
4. **Session issues**: Check your session configuration

### Debug Commands

```bash
# Check if routes are registered
php artisan route:list --name=twofactor

# Check if middleware is available
php artisan route:list --middleware=2fa

# Clear cache if needed
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Next Steps

Once you've successfully tested the basic functionality:

1. **Customize the views** to match your application's design
2. **Configure the package** using the `config/twofactor.php` file
3. **Add additional security features** like backup codes
4. **Implement user management** for 2FA settings
5. **Add comprehensive tests** for your implementation

## Support

If you encounter any issues:

1. Check the Laravel logs: `storage/logs/laravel.log`
2. Verify all dependencies are installed correctly
3. Ensure your Laravel version is compatible
4. Check the package documentation for configuration options

---

This example setup provides a complete working implementation of the Laravel 2FA package. You can use this as a starting point for your own applications.
