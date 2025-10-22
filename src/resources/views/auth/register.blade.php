@extends('layouts.app')

@section('title', '会員登録')

@push('page-css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endpush

@section('content')
<h1 class="register-title">会員登録</h1>

<form action="{{ route('register.post') }}" method="POST" class="register-form" novalidate>
    @csrf
    <div class="form-group">
        <label for="name">名前</label>
        <input type="text" id="name" name="name"
            value="{{ old('name') }}">
        @error('name')
        <p class="error-message">{{ $message }}</p>
        @enderror
    </div>

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

    <div class="form-group">
        <label for="password_confirmation">パスワード確認</label>
        <input type="password" id="password_confirmation" name="password_confirmation"
            value="{{ old('password_confirmation') }}">
        @error('password_confirmation')
        <p class="error-message">{{ $message }}</p>
        @enderror
    </div>

    <button type="submit" class="register-button">登録する</button>
</form>

<p class="login-link">
    <a href="/login">ログインはこちら</a>
</p>
@endsection