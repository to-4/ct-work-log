<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('ユーザーID')
                ->constrained(table: 'users', column: 'id')
                ->restrictOnDelete();
            $table->date('work_date')->comment('日付');
            $table->timestamp('clock_in_at')->nullable()->comment('出勤時刻');
            $table->timestamp('clock_out_at')->nullable()->comment('退勤時刻');
            $table->foreignId('attendance_status_id')->comment('ステータスID')
                ->constrained(table: 'attendance_statuses', column: 'id')
                ->restrictOnDelete();
            $table->text('note')->nullable()->comment('備考');
            $table->integer('working_minutes')->nullable()->comment('勤務時間');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
