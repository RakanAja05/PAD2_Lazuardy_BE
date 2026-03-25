<?php

use App\Enums\TutorStatusEnum;
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
        $statuses = TutorStatusEnum::list();

        Schema::create('tutor_confirms', function (Blueprint $table) use ($statuses)
        {
            $table->id();
            $table->foreignId('student_id')->constrained('students', 'user_id')->cascadeOnDelete();
            $table->foreignId('tutor_id')->constrained('tutors', 'user_id')->cascadeOnDelete();
            $table->foreignId('schedule_tutor_id')->constrained('schedule_tutors', 'id')->cascadeOnDelete();
            $table->string('reason')->nullable();
            $table->string('address');
            $table->enum('status', $statuses)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutor_confirms');
    }
};
