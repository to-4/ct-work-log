@extends('layouts.app_admin')

@section('title', '勤怠一覧')

@push('page-css')
<link rel="stylesheet" href="{{ asset('css/admin/attendances/list.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@endpush

@section('content')
<main class="attendance-list">
    <h1 class="attendance-list__title">勤怠一覧</h1>

    {{-- 日切り替え --}}
    <div class="date-switch-wrapper">
        <div class="date-switch">
            <a href="{{ route('admin.attendance.list', ['date' => $prevDate->format('Y-m-d')]) }}" class="date-switch__btn date-switch__btn--left">
                <img src="{{ asset('images/arrow_icon.png') }}" class="icon icon--arrow-left"> 前日
            </a>

            <div class="date-switch__current">
                <img src="{{ asset('images/calender_icon.png') }}" class="icon icon--calendar">
                <span>{{ $targetDate->format('Y/m/d') }}</span>
            </div>

            <a href="{{ route('admin.attendance.list', ['date' => $nextDate->format('Y-m-d')]) }}" class="date-switch__btn date-switch__btn--right">
                翌日 <img src="{{ asset('images/arrow_icon.png') }}" class="icon icon--arrow-right">
            </a>
        </div>
    </div>

    {{-- 勤怠テーブル --}}
    <table class="attendance-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendances as $attendance)
            <tr>
                <td>{{ $attendance->user->name ? $attendance->user->name : '' }}</td>
                <td>{{ $attendance->clock_in_at ? $attendance->clock_in_at : '' }}</td>
                <td>{{ $attendance->clock_out_at ? $attendance->clock_out_at : '' }}</td>
                <td>{{ $attendance->break_minutes ? floor($attendance->break_minutes / 60) . ':' . str_pad($attendance->break_minutes % 60, 2, '0', STR_PAD_LEFT) : '' }}</td>
                <td>{{ $attendance->working_minutes ? floor($attendance->working_minutes / 60) . ':' . str_pad($attendance->working_minutes % 60, 2, '0', STR_PAD_LEFT) : '' }}</td>
                <td><a href="{{ route('admin.attendance.detail', ['id' => $attendance->id ? $attendance->id : '0']) }}" class="link-detail">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</main>
@endsection