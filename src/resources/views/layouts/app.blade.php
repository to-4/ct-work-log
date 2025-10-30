{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'COACHTECH 管理システム')</title>

    {{-- 共通CSS --}}
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    {{-- ページ固有CSS --}}
    @stack('page-css')
</head>

<body>
    {{-- ヘッダー部分 --}}
    <header class="header">
        <div class="header__inner">
            <div class="header__logo">
                <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH">
            </div>

            <nav class="header__nav">
                <ul>
                    @if (Auth::check())
                    <li><a href="{{ url('/attendance') }}">勤怠</a></li>
                    <li><a href="{{ url('/attendance/list') }}">勤怠一覧</a></li>
                    <li><a href="{{ url('/stamp_correction_request/list') }}">申請</a></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="logout-button">ログアウト</button>
                        </form>
                    </li>
                    @endif
                </ul>
            </nav>
        </div>
    </header>
    {{-- メインコンテンツ --}}
    <main class="main">

        {{-- フラッシュメッセージ（任意） --}}
        @if(session('success'))
        <div class="flash flash-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="flash flash-error">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>
</body>

</html>