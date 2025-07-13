<?php

/**
 * Laravel 2FA Package Test Script
 * 
 * This script helps you test the 2FA package functionality.
 * Run this in your Laravel application root directory.
 */

echo "🔐 Laravel 2FA Package Test Script\n";
echo "===================================\n\n";

// Check if we're in a Laravel application
if (!file_exists('artisan')) {
    echo "❌ Error: This script must be run from a Laravel application root directory.\n";
    echo "Please navigate to your Laravel project directory and run this script again.\n";
    exit(1);
}

echo "✅ Laravel application detected.\n\n";

// Test 1: Check if package is installed
echo "1. Checking package installation...\n";
if (file_exists('vendor/mannaf/laravel-2fa')) {
    echo "   ✅ Package is installed.\n";
} else {
    echo "   ❌ Package not found. Please install it first:\n";
    echo "   composer require mannaf/laravel-2fa\n";
    exit(1);
}

// Test 2: Check if config is published
echo "\n2. Checking configuration...\n";
if (file_exists('config/twofactor.php')) {
    echo "   ✅ Configuration file exists.\n";
} else {
    echo "   ⚠️  Configuration file not found. Publishing...\n";
    exec('php artisan vendor:publish --tag=twofactor-config', $output, $returnCode);
    if ($returnCode === 0) {
        echo "   ✅ Configuration published successfully.\n";
    } else {
        echo "   ❌ Failed to publish configuration.\n";
        exit(1);
    }
}

// Test 3: Check if views are published
echo "\n3. Checking views...\n";
if (file_exists('resources/views/vendor/twofactor')) {
    echo "   ✅ Views are published.\n";
} else {
    echo "   ⚠️  Views not found. Publishing...\n";
    exec('php artisan vendor:publish --tag=twofactor-views', $output, $returnCode);
    if ($returnCode === 0) {
        echo "   ✅ Views published successfully.\n";
    } else {
        echo "   ❌ Failed to publish views.\n";
        exit(1);
    }
}

// Test 4: Check database migration
echo "\n4. Checking database migration...\n";
$migrationExists = false;
$migrationFiles = glob('database/migrations/*_add_two_factor_to_users_table.php');
if (!empty($migrationFiles)) {
    echo "   ✅ Migration file exists.\n";
    $migrationExists = true;
} else {
    echo "   ⚠️  Migration file not found.\n";
    echo "   Creating migration...\n";

    $migrationContent = '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTwoFactorToUsersTable extends Migration
{
    public function up()
    {
        Schema::table(\'users\', function (Blueprint $table) {
            $table->string(\'two_factor_secret\')->nullable();
            $table->boolean(\'two_factor_enabled\')->default(false);
        });
    }

    public function down()
    {
        Schema::table(\'users\', function (Blueprint $table) {
            $table->dropColumn([\'two_factor_secret\', \'two_factor_enabled\']);
        });
    }
}';

    $timestamp = date('Y_m_d_His');
    $migrationPath = "database/migrations/{$timestamp}_add_two_factor_to_users_table.php";

    if (file_put_contents($migrationPath, $migrationContent)) {
        echo "   ✅ Migration file created: {$migrationPath}\n";
        $migrationExists = true;
    } else {
        echo "   ❌ Failed to create migration file.\n";
        exit(1);
    }
}

// Test 5: Run migration
echo "\n5. Running migration...\n";
exec('php artisan migrate', $output, $returnCode);
if ($returnCode === 0) {
    echo "   ✅ Migration completed successfully.\n";
} else {
    echo "   ❌ Migration failed.\n";
    echo "   Output: " . implode("\n   ", $output) . "\n";
    exit(1);
}

// Test 6: Check routes
echo "\n6. Checking routes...\n";
exec('php artisan route:list --name=twofactor', $output, $returnCode);
if ($returnCode === 0 && !empty($output)) {
    echo "   ✅ 2FA routes are registered:\n";
    foreach ($output as $line) {
        if (strpos($line, 'twofactor') !== false) {
            echo "      " . trim($line) . "\n";
        }
    }
} else {
    echo "   ❌ 2FA routes not found. Check your route registration.\n";
}

// Test 7: Check middleware
echo "\n7. Checking middleware...\n";
exec('php artisan route:list --middleware=2fa', $output, $returnCode);
if ($returnCode === 0) {
    echo "   ✅ 2FA middleware is available.\n";
} else {
    echo "   ❌ 2FA middleware not found.\n";
}

echo "\n🎉 Package setup completed!\n\n";

echo "📋 Next Steps:\n";
echo "==============\n";
echo "1. Add the 2FA middleware to your routes:\n";
echo "   Route::middleware(['auth', '2fa'])->group(function () {\n";
echo "       // Your protected routes\n";
echo "   });\n\n";

echo "2. Test the setup page:\n";
echo "   - Visit: /2fa/setup (when logged in)\n";
echo "   - Scan QR code with Google Authenticator\n";
echo "   - Enter the 6-digit code to enable 2FA\n\n";

echo "3. Test the verification flow:\n";
echo "   - Log out and log back in\n";
echo "   - You should be redirected to /2fa/verify\n";
echo "   - Enter the code from your authenticator app\n\n";

echo "4. Test protected routes:\n";
echo "   - Try accessing a route with 2fa middleware\n";
echo "   - Should redirect to verification if 2FA is enabled\n\n";

echo "🔧 Configuration:\n";
echo "=================\n";
echo "Edit config/twofactor.php to customize:\n";
echo "- Redirect URLs\n";
echo "- QR code settings\n";
echo "- Google2FA settings\n";
echo "- Session settings\n\n";

echo "📱 Supported Authenticator Apps:\n";
echo "================================\n";
echo "- Google Authenticator\n";
echo "- Authy\n";
echo "- Microsoft Authenticator\n";
echo "- Any TOTP-compatible app\n\n";

echo "🚨 Troubleshooting:\n";
echo "===================\n";
echo "If you encounter issues:\n";
echo "1. Check Laravel logs: storage/logs/laravel.log\n";
echo "2. Verify database columns exist\n";
echo "3. Check route registration\n";
echo "4. Ensure session configuration is correct\n\n";

echo "✅ Test script completed successfully!\n";
