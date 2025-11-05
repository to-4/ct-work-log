@extends('layouts.app_admin')

@section('title', '勤怠詳細')

@push('page-css')
<link rel="stylesheet" href="{{ asset('css/admin/requests/approve.css') }}">
@endpush

@section('content')
<main class="attendance-detail">
    <h1 class="attendance-detail__title">勤怠詳細</h1>

    <form method="POST" action="{{ route('admin.request.approve.update', $correctionRequest->id) }}">
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
                <div class="attendance-detail__value letter_spacing">
                    {{ $attendance->work_date->format('Y年n月j日') }}
                </div>
            </div>

            {{-- 出勤・退勤 --}}
            <div class="attendance-detail__row">
                <div class="attendance-detail__label">出勤・退勤</div>
                <div class="attendance-detail__value">
                    <div class="input-pair">
                        <div class="attendance-detail__value_start">
                            {{ old('clock_in_at', substr($attendance->clock_in_at ?? '', 0, 5)) }}
                        </div>
                        <div class="attendance-detail__value_delim">
                            <span>〜</span>
                        </div>
                        <div class="attendance-detail__value_end">
                            {{ old('clock_out_at', substr($attendance->clock_out_at ?? '', 0, 5)) }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- 休憩 --}}
            @foreach ($attendance->attendanceBreaks as $index => $break)
            @php
            $breakId = $break->id;
            @endphp
            <div class=" attendance-detail__row">
                <div class="attendance-detail__label">
                    休憩{{ $index > 0 ? $index + 1 : '' }}
                </div>
                <div class="attendance-detail__value">
                    <div class="input-pair">
                        <div class="attendance-detail__value_start">
                            {{ old("breaks.$breakId.break_start_at", substr($break->break_start_at ?? '', 0, 5)) }}
                        </div>
                        <div class="attendance-detail__value_delim">
                            <span>〜</span>
                        </div>
                        <div class="attendance-detail__value_end">
                            {{ old("breaks.$breakId.break_end_at", substr($break->break_end_at ?? '', 0, 5)) }}
                        </div>
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
                        <div class="attendance-detail__value_start">
                            {{ old('breaks.new.break_start_at') }}
                        </div>
                        <div class="attendance-detail__value_delim">
                            <span>〜</span>
                        </div>
                        <div class="attendance-detail__value_end">
                            {{ old('breaks.new.break_end_at') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- 備考 --}}
            <div class="attendance-detail__row">
                <div class="attendance-detail__label">備考</div>
                <div class="attendance-detail__value">
                    {{ old('note', $attendance->note) }}
                </div>
            </div>
        </div>
        <div class="attendance-detail__actions">

            @if (empty($correctionRequest->approved_at))
            {{-- 修正ボタン --}}
            <button type="submit" class="btn btn-primary">修正</button>
            @else
            {{-- 承認済みボタン（非クリック・装飾済み） --}}
            <p class="btn-approved">承認済み</p>
            @endif
        </div>
    </form>
</main>
@endsection