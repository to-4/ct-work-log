<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * AttendanceBreak model
 *
 * 勤怠休憩情報
 *
 * Corresponding table: attendance_breaks
 *
 * Properties:
 *
 * @property int $id
 *     主キーID（自動採番）
 *
 * @property BIGINT UNSIGNED $attendance_id
 *     勤怠データID
 *
 * @property TIME $break_start_at
 *     休憩開始時刻
 *     HH:mm ※ 秒は切り捨て
 *
 * @property TIME|null $break_end_at
 *     休憩終了時刻
 *     HH:mm ※ 秒は切り捨て
 *
 * @property INT|null $break_minutes
 *     休憩時間
 *     単位：分
 *     一覧・集計画面で頻繁に使用するため、再計算を避けるためテーブルに記録
 *
 * @property Carbon|null $created_at
 *     タスクが作成された日時（Laravelが自動で管理）
 *
 * @property Carbon|null $updated_at
 *     タスクが最後に更新された日時（Laravelが自動で管理）
 */
class AttendanceBreak extends Model
{
    /** @use HasFactory<\Database\Factories\AttendanceBreakFactory> */
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'break_start_at',
        'break_end_at',
        'break_minutes',
    ];

    protected $casts = [
        'break_start_at' => 'string',
        'break_end_at' => 'string',
    ];

    // リレーション定義メソッド

    // 子（多）→ 親（1）
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
