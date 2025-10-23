@php
use App\Models\AttendanceStatus;
@endphp

@extends('layouts.app')

@section('title', '勤怠登録')

@push('page-css')
<link rel="stylesheet" href="{{ asset('css/attendances/index.css') }}">
@endpush

@section('content')
<div class="attendance">
    <div class="attendance__inner">

        {{-- ステータス表示 --}}
        <div class="attendance__status">
            <span class="status-label">{{ $attendance->attendanceStatus->name ?? '勤務外' }}</span>
        </div>

        {{-- 日付と時刻 --}}
        <div class="attendance__date">
            {{ now()->format('Y年n月j日(D)') }}
        </div>

        <div class="attendance__time">
            {{ now()->format('H:i') }}
        </div>

        {{-- ステータス別表示 --}}
        <div class="attendance__actions">
            @switch($attendance->attendance_status_id)
            @case(AttendanceStatus::OFF_DUTY)
            {{-- 出勤前 --}}
            <form method="POST" action="{{ route('test') }} {{--{{ route('attendance.start') }}--}}">
                @csrf
                <button type="submit" class="btn btn-primary">出勤</button>
            </form>
            @break

            @case(AttendanceStatus::WORKING)
            {{-- 出勤中 --}}
            <div class="button-group">
                <form method="POST" action="#{{--{{ route('attendance.end') }}--}}">
                    @csrf
                    <button type="submit" class="btn btn-primary">退勤</button>
                </form>
                <form method="POST" action="#{{--{{ route('attendance.break.start') }}--}}">
                    @csrf
                    <button type="submit" class="btn btn-secondary">休憩入</button>
                </form>
            </div>
            @break

            @case(AttendanceStatus::ON_BREAK)
            {{-- 休憩中 --}}
            <form method="POST" action="#{{--{{ route('attendance.break.end') }}--}}">
                @csrf
                <button type="submit" class="btn btn-secondary">休憩戻</button>
            </form>
            @break

            @case(AttendanceStatus::COMPLETED)
            {{-- 退勤後 --}}
            <p class="attendance__message">お疲れ様でした。</p>
            @break
            @endswitch
        </div>

    </div>
</div>
@endsection