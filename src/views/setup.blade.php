<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Two-Factor Authentication</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .qr-container {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #f8f9fa;
        }

        .secret-key {
            font-family: monospace;
            background: #e9ecef;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            word-break: break-all;
        }

        .form-container {
            max-width: 400px;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">Setup Two-Factor Authentication</h3>
                    </div>
                    <div class="card-body">
                        @if(session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                        @endif

                        <div class="alert alert-info">
                            <strong>Step 1:</strong> Scan the QR code below with your authenticator app (Google Authenticator, Authy, Microsoft Authenticator, etc.)
                        </div>

                        <div class="qr-container">
                            {!! $qrCode !!}
                        </div>

                        <div class="alert alert-warning">
                            <strong>Step 2:</strong> If you can't scan the QR code, manually enter this secret key in your authenticator app:
                            <div class="secret-key">{{ $secret }}</div>
                        </div>

                        <div class="form-container">
                            <form method="POST" action="{{ route('twofactor.enable') }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="code" class="form-label">Enter the 6-digit code from your authenticator app:</label>
                                    <input type="text"
                                        class="form-control @error('code') is-invalid @enderror"
                                        id="code"
                                        name="code"
                                        required
                                        maxlength="6"
                                        pattern="\d{6}"
                                        placeholder="000000"
                                        autocomplete="off">
                                    @error('code')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Enable Two-Factor Authentication</button>
                                </div>
                            </form>
                        </div>

                        <div class="mt-4 text-center">
                            <a href="{{ config('twofactor.redirect_after_setup', '/home') }}" class="btn btn-outline-secondary">
                                Skip for now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-focus on code input
        document.getElementById('code').focus();

        // Auto-format code input
        document.getElementById('code').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
        });
    </script>
</body>

</html>