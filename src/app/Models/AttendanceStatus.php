<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * AttendanceStatus model
 *
 * 勤怠ステータス情報
 *
 * Corresponding table: attendance_statuss
 *
 * Properties:
 *
 * @property int $id
 *     主キーID（自動採番）
 *
 * @property VARCHAR(255) $name
 *     ステータス名
 *
 * @property Carbon|null $created_at
 *     タスクが作成された日時（Laravelが自動で管理）
 *
 * @property Carbon|null $updated_at
 *     タスクが最後に更新された日時（Laravelが自動で管理）
 */
class AttendanceStatus extends Model
{

    protected $table = 'attendance_statuses';

    const OFF_DUTY   = 1; // 勤務外
    const WORKING    = 2; // 出勤中
    const ON_BREAK   = 3; // 休憩中
    const COMPLETED  = 4; // 退勤済

    use HasFactory;

    protected $fillable = ['name'];

    // 親（1） → 子（多）
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // エイリアスメソッド

    /**
     * 勤務外フラグ
     * true の場合、ステータスが勤務外
     *
     * @return boolean
     */
    public function isOffDuty(): bool
    {
        return $this->status === AttendanceStatus::OFF_DUTY
                || !$this->status; // ステータスが null / 0 / false / ''
    }

    /**
     * 出勤中フラグ
     * true の場合、ステータスが出勤中
     *
     * @return boolean
     */
    public function isWorking(): bool
    {
        return $this->status === AttendanceStatus::WORKING;
    }

    /**
     * 休憩中フラグ
     * true の場合、ステータスが休憩中
     *
     * @return boolean
     */
    public function isOnBreak(): bool
    {
        return $this->status === AttendanceStatus::ON_BREAK;
    }

    /**
     * 退勤済フラグ
     * true の場合、ステータスが退勤済
     *
     * @return boolean
     */
    public function isCompleted(): bool
    {
        return $this->status === AttendanceStatus::COMPLETED;
    }

}

