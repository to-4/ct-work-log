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
        Schema::create('attendance_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->comment('勤怠データID')
                ->constrained(table: 'attendances', column: 'id')
                ->restrictOnDelete();
            $table->string('break_start_at')->comment('休憩開始時刻');
            $table->string('break_end_at')->nullable()->comment('休憩終了時刻');
            $table->integer('break_minutes')->nullable()->comment('休憩時間');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_breaks');
    }
};
