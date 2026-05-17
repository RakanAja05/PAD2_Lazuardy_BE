<?php

use App\Enums\TakenScheduleStatusEnum;
use App\Enums\TutorStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $statuses = TakenScheduleStatusEnum::list();

        Schema::create('schedules', function (Blueprint $table) use ($statuses) {
            $table->id();
            $table->foreignId('student_id')->constrained('students', 'user_id')->cascadeOnDelete();
            $table->foreignId('tutor_id')->constrained('tutors', 'user_id')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects');
            // Keep `date` as timestamp (can include time component for compatibility)
            $table->timestamp('date')->nullable();
            $table->time('time')->nullable();
            $table->string('reason')->nullable();
            $table->string('address');
            $table->enum('status', $statuses)->nullable();
            $table->timestamps();

            $table->index(['tutor_id', 'date']);
            $table->index(['student_id', 'date']);
        });

        $now = now();

        // 1) Copy taken_schedules into schedules (preserve IDs for downstream references like presences)
        if (Schema::hasTable('taken_schedules')) {
            $rows = DB::table('taken_schedules')
                ->leftJoin('schedule_tutors', 'taken_schedules.schedule_tutor_id', '=', 'schedule_tutors.id')
                ->select([
                    'taken_schedules.id as id',
                    'taken_schedules.student_id as student_id',
                    'schedule_tutors.tutor_id as tutor_id',
                    'taken_schedules.subject_id as subject_id',
                    'taken_schedules.date as date',
                    DB::raw('TIME(taken_schedules.date) as time'),
                    'taken_schedules.address as address',
                    'taken_schedules.status as status',
                ])
                ->orderBy('taken_schedules.id')
                ->get();

            foreach ($rows as $row) {
                if (!$row->tutor_id) {
                    continue;
                }

                $status = $row->status ?: TakenScheduleStatusEnum::PENDING->value;

                DB::table('schedules')->updateOrInsert(
                    ['id' => $row->id],
                    [
                        'student_id' => $row->student_id,
                        'tutor_id' => $row->tutor_id,
                        'subject_id' => $row->subject_id,
                        'date' => $row->date,
                        'time' => $row->time,
                        'reason' => null,
                        'address' => $row->address,
                        'status' => $status,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }

        // 2) Copy tutor_confirms into schedules (IDs are offset to avoid collision)
        if (Schema::hasTable('tutor_confirms')) {
            $maxScheduleId = (int) (DB::table('schedules')->max('id') ?? 0);

            $rows = DB::table('tutor_confirms')
                ->leftJoin('schedule_tutors', 'tutor_confirms.schedule_tutor_id', '=', 'schedule_tutors.id')
                ->select([
                    'tutor_confirms.id as id',
                    'tutor_confirms.student_id as student_id',
                    'tutor_confirms.tutor_id as tutor_id',
                    DB::raw('tutor_confirms.created_at as date'),
                    'schedule_tutors.time as time',
                    'tutor_confirms.reason as reason',
                    'tutor_confirms.address as address',
                    'tutor_confirms.status as status',
                    'tutor_confirms.created_at as created_at',
                    'tutor_confirms.updated_at as updated_at',
                ])
                ->orderBy('tutor_confirms.id')
                ->get();

            foreach ($rows as $row) {
                $mappedStatus = match ((string) $row->status) {
                    TutorStatusEnum::PENDING->value => TakenScheduleStatusEnum::PENDING->value,
                    TutorStatusEnum::VERIFIED->value => TakenScheduleStatusEnum::ACTIVE->value,
                    TutorStatusEnum::REJECTED->value => TakenScheduleStatusEnum::REJECTED->value,
                    default => TakenScheduleStatusEnum::PENDING->value,
                };

                DB::table('schedules')->insert([
                    'id' => $maxScheduleId + (int) $row->id,
                    'student_id' => $row->student_id,
                    'tutor_id' => $row->tutor_id,
                    'subject_id' => null,
                    'date' => $row->date,
                    'time' => $row->time,
                    'reason' => $row->reason,
                    'address' => $row->address,
                    'status' => $mappedStatus,
                    'created_at' => $row->created_at ?? $now,
                    'updated_at' => $row->updated_at ?? $now,
                ]);
            }
        }

        // 3) Re-point presences FK from taken_schedules -> schedules, keep column name `taken_schedule_id`
        if (Schema::hasTable('presences')) {
            Schema::table('presences', function (Blueprint $table) {
                try {
                    $table->dropForeign(['taken_schedule_id']);
                } catch (\Throwable $e) {
                    // no-op
                }
            });

            Schema::table('presences', function (Blueprint $table) {
                $table->foreign('taken_schedule_id')
                    ->references('id')
                    ->on('schedules');
            });
        }

        // 4) Drop old tables (data already copied)
        Schema::dropIfExists('tutor_confirms');
        Schema::dropIfExists('taken_schedules');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
