{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'COACHTECH 管理システム')</title>

    {{-- 共通CSS --}}
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common_admin.css') }}">
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
                    @if (Auth::check() && Auth::user()->is_admin)
                    <li><a href="{{ url('/attendance') }}">勤怠一覧</a></li>
                    <li><a href="{{ url('/attendance/list') }}">スタッフ一覧</a></li>
                    <li><a href="{{ url('/stamp_correction_request/list') }}">申請一覧</a></li>
                    <li>
                        <form method="POST" action="{{ route('admin.logout') }}">
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
        @yield('content')
    </main>
</body>

</html>