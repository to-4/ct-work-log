<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon; // 日付操作ライブラリ

/**
 * AttendanceCorrectionRequest model
 *
 * 勤怠情報の修正申請情報
 *
 * Corresponding table: attendance_correction_requests
 *
 * Properties:
 *
 * @property int $id
 *     主キーID（自動採番）
 *
 * @property BIGINT UNSIGNED $attendance_id
 *     勤怠情報ID
 *
 * @property TIMESTAMP $requested_at
 *     申請日時
 *
 * @property BIGINT UNSIGNED|null $approved_by
 *     承認ユーザーID
 *
 * @property TIMESTAMP|null $approved_at
 *     承認日時
 *
 * @property Carbon|null $created_at
 *     タスクが作成された日時（Laravelが自動で管理）
 *
 * @property Carbon|null $updated_at
 *     タスクが最後に更新された日時（Laravelが自動で管理）
 */
class AttendanceCorrectionRequest extends Model
{
    /** @use HasFactory<\Database\Factories\AttendanceCorrectionRequestFactory> */
    use HasFactory;

    protected $table = 'attendance_correction_requests';

    protected $fillable = [
        'attendance_id',
        'requested_at',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'requested_at' => 'timestamp',
        'approved_at' => 'timestamp',
    ];

    // 子（多）→ 親（0|1）
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
