@extends('layouts.app')

@section('title', 'メール認証')

@push('page-css')
<link rel="stylesheet" href="{{ asset('css/auth/verify-code.css') }}">
@endpush

@section('content')
<div class="verify-wrapper">
    <div class="verify-box">
        <h2 class="verify-title">メール認証</h2>

        <p class="verify-text">
            登録メールアドレス宛に <strong>6桁の認証コード</strong> を送信しました。<br>
            受信したコードを入力して認証を完了してください。
        </p>

        <form method="POST" action="{{ route('verification.code.check') }}" class="verify-form">
            @csrf
            <div class="input-group">
                <input type="text"
                    name="code"
                    maxlength="6"
                    required
                    pattern="\d{6}"
                    placeholder="123456"
                    class="verify-input"
                    autofocus>
                <button type="submit" class="btn-verify">認証する</button>
            </div>

            @error('code')
            <p class="error-message">{{ $message }}</p>
            @enderror
        </form>

        <form method="POST" action="{{ route('verification.code.resend') }}" class="resend-form">
            @csrf
            <button type="submit" class="btn-resend">認証コードを再送する</button>
        </form>
    </div>
</div>
@endsection