<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .verification-container {
            max-width: 400px;
            margin: 0 auto;
        }

        .auth-icon {
            font-size: 3rem;
            color: #007bff;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <div class="auth-icon">🔐</div>
                        <h3 class="mb-0">Two-Factor Authentication</h3>
                    </div>
                    <div class="card-body">
                        @if(session('message'))
                        <div class="alert alert-info">
                            {{ session('message') }}
                        </div>
                        @endif

                        @if(session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                        @endif

                        <div class="alert alert-warning">
                            <strong>Security Check Required:</strong> Please enter the 6-digit code from your authenticator app to continue.
                        </div>

                        <div class="verification-container">
                            <form method="POST" action="{{ route('twofactor.verify.post') }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="code" class="form-label">Authentication Code:</label>
                                    <input type="text"
                                        class="form-control form-control-lg text-center @error('code') is-invalid @enderror"
                                        id="code"
                                        name="code"
                                        required
                                        maxlength="6"
                                        pattern="\d{6}"
                                        placeholder="000000"
                                        autocomplete="off"
                                        autofocus>
                                    @error('code')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">Verify & Continue</button>
                                </div>
                            </form>
                        </div>

                        <div class="mt-4 text-center">
                            <small class="text-muted">
                                Having trouble?
                                <a href="{{ route('login') }}">Try logging in again</a>
                            </small>
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

        // Auto-submit when 6 digits are entered
        document.getElementById('code').addEventListener('input', function(e) {
            if (this.value.length === 6) {
                this.form.submit();
            }
        });
    </script>
</body>

</html>