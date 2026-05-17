<?php

namespace App\Models;

use App\Enums\TakenScheduleStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TutorConfirm extends Model
{
    /** @use HasFactory<\Database\Factories\TutorConfirmFactory> */
    use HasFactory;

    protected $table = 'schedules';

    protected $fillable =
    [
        'student_id',
        'tutor_id',
        'subject_id',
        'reason',
        'address',
        'date',
        'time',
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

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'user_id');
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class, 'tutor_id', 'user_id');
    }
}
