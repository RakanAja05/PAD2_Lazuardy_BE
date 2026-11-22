<?php

namespace App\Models;

use App\Enums\TakenScheduleStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TakenSchedule extends Model
{
    /** @use HasFactory<\Database\Factories\TakenScheduleFactory> */
    use HasFactory;

    protected $table = 'schedules';

    protected $fillable =
    [
        'student_id',
        'tutor_id',
        'subject_id',
        'date',
        'time',
        'reason',
        'address',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => TakenScheduleStatusEnum::class,
            'date' => 'datetime',
            'time' => 'string',
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

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_id', 'id');
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'user_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
