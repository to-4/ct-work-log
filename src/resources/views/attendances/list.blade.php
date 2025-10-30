@extends('layouts.app')

@section('title', '勤怠一覧')

@push('page-css')
<link rel="stylesheet" href="{{ asset('css/attendances/list.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@endpush

@section('content')
<main class="attendance-list">
    <h1 class="attendance-list__title">勤怠一覧</h1>

    {{-- 月切り替え --}}
    <div class="month-switch-wrapper">
        <div class="month-switch">
            <a href="{{ route('attendance.list', ['month' => $prevMonth->format('Y-m')]) }}" class="month-switch__btn month-switch__btn--left">
                <i class="fa-solid fa-angle-left"></i> 前月
            </a>

            <div class="month-switch__current">
                <i class="fa-solid fa-calendar-days"></i>
                <span>{{ $targetMonth->format('Y/m') }}</span>
            </div>

            <a href="{{ route('attendance.list', ['month' => $nextMonth->format('Y-m')]) }}" class="month-switch__btn month-switch__btn--right">
                翌月 <i class="fa-solid fa-angle-right"></i>
            </a>
        </div>
    </div>

    {{-- 勤怠テーブル --}}
    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
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
                <td>{{ $attendance->work_date->format('m/d') }}({{ $attendance->work_date->isoFormat('dd') }})</td>
                <td>{{ $attendance->clock_in_at ? $attendance->clock_in_at : '' }}</td>
                <td>{{ $attendance->clock_out_at ? $attendance->clock_out_at : '' }}</td>
                <td>{{ $attendance->break_minutes ? floor($attendance->break_minutes / 60) . ':' . str_pad($attendance->break_minutes % 60, 2, '0', STR_PAD_LEFT) : '' }}</td>
                <td>{{ $attendance->working_minutes ? floor($attendance->working_minutes / 60) . ':' . str_pad($attendance->working_minutes % 60, 2, '0', STR_PAD_LEFT) : '' }}</td>
                <td><a href="{{ route('attendance.detail', ['id' => $attendance->id ? $attendance->id : '0']) }}" class="link-detail">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</main>
@endsection