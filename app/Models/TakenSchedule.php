<?php

namespace App\Models;

use App\Enums\TakenScheduleStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class TakenSchedule extends Model
{
    /** @use HasFactory<\Database\Factories\TakenScheduleFactory> */
    use HasFactory;
    public $timestamps = false;

    protected $fillable =
    [
        'student_id',
        'schedule_tutor_id',
        'subject_id',
        'date',
        'address',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => TakenScheduleStatusEnum::class,
            'date' => 'datetime',
        ];
    }

    public function studentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id', 'id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id', 'id');
    }

    public function tutor(): HasOneThrough
    {
        // Resolve tutor user through scheduleTutor: taken_schedules.schedule_tutor_id -> schedule_tutors.id -> schedule_tutors.tutor_id -> users.id
        return $this->hasOneThrough(
            User::class,
            ScheduleTutor::class,
            'id',
            'id',
            'schedule_tutor_id',
            'tutor_id'
        );
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'user_id');
    }

    public function scheduleTutor(): BelongsTo
    {
        return $this->belongsTo(ScheduleTutor::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
