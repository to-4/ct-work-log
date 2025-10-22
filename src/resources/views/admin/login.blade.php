@extends('layouts.app_admin')

@section('title', '管理者ログイン')

@push('page-css')
<link rel="stylesheet" href="{{ asset('css/admin/login.css') }}">
@endpush

@section('content')
<h1 class="login-title">管理者ログイン</h1>

<form action="{{ route('admin.login.post') }}" method="POST" class="login-form" novalidate>
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
@endsection