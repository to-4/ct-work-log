@extends('layouts.app')

@section('title', '勤怠詳細')

@push('page-css')
<link rel="stylesheet" href="{{ asset('css/attendances/detail.css') }}">
@endpush

@section('content')
<main class="attendance-detail">
    <h1 class="attendance-detail__title">勤怠詳細</h1>

    <form method="POST" action="{{ route('attendance.detail.update', $attendance->id) }}">
        @csrf
        @method('PUT')

        <div class="attendance-detail__table">

            {{-- 名前 --}}
            <div class="attendance-detail__row">
                <div class="attendance-detail__label">名前</div>
                <div class="attendance-detail__value">{{ $attendance->user->name }}</div>
            </div>

            {{-- 日付 --}}
            <div class="attendance-detail__row">
                <div class="attendance-detail__label">日付</div>
                <div class="attendance-detail__value">
                    {{ $attendance->work_date->format('Y年n月j日') }}
                </div>
            </div>

            {{-- 出勤・退勤 --}}
            <div class="attendance-detail__row">
                <div class="attendance-detail__label">出勤・退勤</div>
                <div class="attendance-detail__value">
                    <div class="input-pair">
                        <input type="text"
                            name="clock_in_at"
                            value="{{ old('clock_in_at', substr($attendance->clock_in_at ?? '', 0, 5)) }}">
                        <span>〜</span>
                        <input type="text"
                            name="clock_out_at"
                            value="{{ old('clock_out_at', substr($attendance->clock_out_at ?? '', 0, 5)) }}">
                    </div>

                    {{-- エラーメッセージを下にまとめて表示 --}}
                    <div class="error-area">
                        @error('clock_in_at')
                        <p class="error-message">{{ $message }}</p>
                        @enderror
                        @error('clock_out_at')
                        <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- 休憩 --}}
            @foreach ($attendance->attendanceBreaks as $index => $break)
            @php
            $breakId = $break->id;
            @endphp
            <div class="attendance-detail__row">
                <div class="attendance-detail__label">
                    休憩{{ $index > 0 ? $index + 1 : '' }}
                </div>
                <div class="attendance-detail__value">
                    <div class="input-pair">
                        <input type="text"
                            name="breaks[{{ $breakId }}][break_start_at]"
                            value="{{ old("breaks.$breakId.break_start_at", substr($break->break_start_at ?? '', 0, 5)) }}">

                        <span>〜</span>
                        <input type="text"
                            name="breaks[{{ $breakId }}][break_end_at]"
                            value="{{ old("breaks.$breakId.break_end_at", substr($break->break_end_at ?? '', 0, 5)) }}">
                    </div>

                    {{-- エラーメッセージを下にまとめて表示 --}}
                    <div class="error-area">
                        @error("breaks.$breakId.break_start_at")
                        <p class="error-message">{{ $message }}</p>
                        @enderror
                        @error("breaks.$breakId.break_end_at")
                        <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            @endforeach
            {{-- 新規追加用の空欄を1行追加 --}}
            <div class="attendance-detail__row">
                <div class="attendance-detail__label">
                    休憩{{ $attendance->attendanceBreaks->count() > 0 ? $attendance->attendanceBreaks->count() + 1 : ''}}
                </div>
                <div class="attendance-detail__value">
                    <div class="input-pair">
                        <input type="text"
                            name="breaks[new][break_start_at]"
                            value="{{ old('breaks.new.break_start_at') }}">
                        <span>〜</span>
                        <input type="text"
                            name="breaks[new][break_end_at]"
                            value="{{ old('breaks.new.break_end_at') }}">
                    </div>

                    {{-- エラーメッセージを下にまとめて表示 --}}
                    <div class="error-area">
                        @error('breaks.new.break_start_at')
                        <p class="error-message">{{ $message }}</p>
                        @enderror
                        @error('breaks.new.break_end_at')
                        <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            {{-- 備考 --}}
            <div class="attendance-detail__row">
                <div class="attendance-detail__label">備考</div>
                <div class="attendance-detail__value">
                    <div class="input-pair">
                        <textarea name="note">{{ old('note', $attendance->note) }}</textarea>
                    </div>
                    <div class="error-area">
                        <div class="">
                            @error('note')
                            <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="attendance-detail__actions">
            @if ($attendance->is_pending_approval)
            <p class="approval-message">承認待ちのため修正はできません。</p>
            @else
            <button type="submit" class="btn btn-primary">修正</button>
            @endif
        </div>
    </form>
</main>
@endsection