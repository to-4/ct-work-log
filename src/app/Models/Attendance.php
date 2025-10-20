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
 * @property TIMESTAMP|null $clock_in_at
 *     出勤時刻
 *
 * @property TIMESTAMP|null $clock_out_at
 *     退勤時刻
 *
 * @property TIMESTAMP|null $break1_start_at
 *     休憩１開始時刻
 *
 * @property TIMESTAMP|null $break1_end_at
 *     休憩１終了時刻
 *
 * @property TIMESTAMP|null $break2_start_at
 *     休憩２開始時刻
 *
 * @property TIMESTAMP|null $break2_end_at
 *     休憩２終了時刻
 *
 * @property TEXT|null $note
 *     備考
 *
 * @property INT|null $working_minutes
 *     勤務時間
 *     勤務時間を分単位で保存（例. 480=8時間）
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
        'break1_start_at',
        'break1_end_at',
        'break2_start_at',
        'break2_end_at',
        'note',
        'working_minutes',
    ];

    protected $casts = [
        'work_date' => 'date',
        'clock_in_at' => 'timestamp',
        'clock_out_at' => 'timestamp',
        'break1_start_at' => 'timestamp',
        'break1_end_at' => 'timestamp',
        'break2_start_at' => 'timestamp',
        'break2_end_at' => 'timestamp',
    ];

    // 親（1） → 子（多）
    public function attendanceCorrectionRequests()
    {
        return $this->hasMany(AttendanceCorrectionRequest::class);
    }

    // 子（多）→ 親（0|1）
    public function user()
    {
        return $this->belongsToMany(User::class);
    }
}
