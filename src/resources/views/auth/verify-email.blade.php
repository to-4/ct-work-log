@extends('layouts.app')

@section('title', 'メール認証誘導画面')

@push('page-css')
<link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}">
@endpush

@section('content')
<div class="verify-container">
    <div class="verify-card">
        <p class="verify-message">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        <form method="GET" action="{{ route('verification.code.notice') }}">
            @csrf
            <button type="submit" class="btn-verify">認証はこちらから</button>
        </form>

        <form method="POST" action="{{ route('verification.code.resend') }}" class="resend-form">
            @csrf
            <button type="submit" class="btn-resend">認証メールを再送する</button>
        </form>
    </div>
</div>
@endsection