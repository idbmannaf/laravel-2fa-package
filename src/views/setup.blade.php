@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Setup Two-Factor Authentication</h2>
    <p>Scan the QR code with your Authenticator app (Google Authenticator, Authy, etc):</p>
    <div>{!! $qrCode !!}</div>
    <p>Or enter this code manually: <strong>{{ $secret }}</strong></p>

    <form method="POST" action="{{ route('twofactor.enable') }}">
        @csrf
        <label>Enter the 6-digit code from your app:</label>
        <input type="text" name="code" required maxlength="6" pattern="\d{6}" />
        @error('code')
        <div class="text-danger">{{ $message }}</div>
        @enderror
        <button type="submit" class="btn btn-primary mt-2">Enable 2FA</button>
    </form>
</div>
@endsection