<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon; // 日付操作ライブラリ

/**
 * Attendance model
 *
 * 出勤情報
 *
 * Corresponding table: attendances
 *
 * Properties:
 *
 * @property int $id
 *     主キーID（自動採番）
 *
 * @property BIGINT UNSIGNED $user_id
 *     ユーザーID
 *
 * @property DATE $work_date
 *     日付
 *     年月日
 *
 * @property TIME|null $clock_in_at
 *     出勤時刻
 *     HH:MM ※ 秒は切り捨て
 *
 * @property TIME|null $clock_out_at
 *     退勤時刻
 *     HH:MM ※ 秒は切り捨て
 *
 * @property BIGINT UNSIGNED|null $attendance_status_id
 *     ステータスID
 *     1:勤務外, 2:勤務中, 3:休憩中, 4:退勤済み
 *
 * @property TEXT|null $note
 *     備考
 *
 * @property INT|null $working_minutes
 *     勤務時間
 *     勤務時間を分単位で保存（例. 480=8時間）
 *
 * @property INT|null $break_minutes
 *     休憩時間
 *     休憩時間を分単位で保存（例. 480=8時間）
 *
 * @property BOOL $is_ppending_approval
 *     承認待ちフラグ
 *     true: 承認待ち、false: 承認待ちではない（デフォルト）
 *
 * @property Carbon|null $created_at
 *     タスクが作成された日時（Laravelが自動で管理）
 *
 * @property Carbon|null $updated_at
 *     タスクが最後に更新された日時（Laravelが自動で管理）
 */
class Attendance extends Model
{
    /** @use HasFactory<\Database\Factories\AttendanceFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in_at',
        'clock_out_at',
        'attendance_status_id',
        'note',
        'working_minutes',
        'break_minutes',
        'is_ppending_approval',
    ];

    protected $casts = [
        'work_date' => 'date',
        'clock_in_at' => 'string',
        'clock_out_at' => 'string',
        'is_ppending_approval' => 'false',
    ];

    // リレーション定義メソッド

    // 子（多）→ 親（0|1）
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 子（多）→ 親（0|1）
    public function attendanceStatus()
    {
        return $this->belongsTo(AttendanceStatus::class);
    }

    // 親（1） → 子（多）
    public function attendanceCorrectionRequests()
    {
        return $this->hasMany(AttendanceCorrectionRequest::class);
    }

    // 親（1） → 子（多）
    public function attendanceBreaks()
    {
        return $this->hasMany(AttendanceBreak::class);
    }
}
