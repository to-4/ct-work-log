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
        Schema::create('attendance_correction_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->comment('勤怠情報ID')
                ->constrained(table: 'attendances', column: 'id')
                ->restrictOnDelete();
            $table->timestamp('requested_at')->comment('申請日時');
            $table->foreignId('approved_by')->nullable()->comment('承認ユーザーID')
                ->constrained(table: 'users', column: 'id')
                ->restrictOnDelete();
            $table->timestamp('approved_at')->nullable()->comment('承認日時');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_correction_requests');
    }
};
