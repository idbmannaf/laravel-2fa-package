@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Two-Factor Authentication Verification</h2>
    <form method="POST" action="{{ route('twofactor.verify.post') }}">
        @csrf
        <label>Enter your 6-digit authentication code:</label>
        <input type="text" name="code" required maxlength="6" pattern="\d{6}" autofocus />
        @error('code')
        <div class="text-danger">{{ $message }}</div>
        @enderror
        <button type="submit" class="btn btn-primary mt-2">Verify</button>
    </form>
</div>
@endsection