@extends('layouts.app')

@section('title', '申請一覧')

@push('page-css')
<link rel="stylesheet" href="{{ asset('css/requests/list.css') }}">
@endpush

@section('content')
<main class="request-list">
    <h1 class="request-list__title">申請一覧</h1>

    {{-- タブ --}}
    <div class="request-list__tabs">
        <a href="{{ route('request.list', ['status' => 'pending']) }}"
            class="tab {{ $status === 'pending' ? 'active' : '' }}">承認待ち</a>
        <a href="{{ route('request.list', ['status' => 'approved']) }}"
            class="tab {{ $status === 'approved' ? 'active' : '' }}">承認済み</a>
    </div>

    {{-- テーブル --}}
    <table class="request-table">
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($requests as $request)
            <tr>
                <td>{{ $request->approved_at ? '承認済み' : '承認待ち' }}</td>
                <td>{{ $request->attendance->user->name ?? '-' }}</td>
                <td>{{ optional($request->attendance->work_date)->format('Y/m/d') }}</td>
                <td>{{ $request->attendance->note ?? '-' }}</td>
                <td>{{ optional($request->requested_at)->format('Y/m/d') }}</td>
                <td>
                    <a href="{{ route('attendance.detail', $request->attendance->id) }}" class="detail-link">詳細</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="empty-message">該当する申請はありません。</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</main>
@endsection