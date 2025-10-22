@extends('layouts.app')

@section('title', 'ログイン')

@push('page-css')
<link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
@endpush

@section('content')
<h1 class="login-title">ログイン</h1>

<form action="{{ route('login.post') }}" method="POST" class="login-form" novalidate>
    @csrf
    <div class="form-group">
        <label for="email">メールアドレス</label>
        <input type="email" id="email" name="email"
            value="{{ old('email') }}">
        @error('email')
        <p class="error-message">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-group">
        <label for="password">パスワード</label>
        <input type="password" id="password" name="password"
            value="{{ old('password') }}">
        @error('password')
        <p class="error-message">{{ $message }}</p>
        @enderror
    </div>

    <button type="submit" class="login-button">ログインする</button>
</form>

<p class="register-link">
    <a href="/register">会員登録はこちら</a>
</p>
@endsection