<?php

use App\Enums\TakenScheduleStatusEnum;
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
        $statuses = TakenScheduleStatusEnum::list();

        Schema::create('taken_schedules', function (Blueprint $table) use ($statuses) {
            $table->id();
            $table->foreignId('student_id')->constrained('students', 'user_id')->cascadeOnDelete();
            $table->foreignId('schedule_tutor_id')->constrained('schedule_tutors', 'id');
            $table->foreignId('subject_id')->constrained('subjects');
            $table->timestamp('date');
            $table->string('address');
            $table->enum('status', $statuses)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taken_schedules');
    }
};
