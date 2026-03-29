<?php

namespace App\Models;

use App\Enums\TutorStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TutorConfirm extends Model
{
    /** @use HasFactory<\Database\Factories\TutorConfirmFactory> */
    use HasFactory;

    protected $fillable =
    [
        'student_id',
        'tutor_id',
        'schedule_tutor_id',
        'reason',
        'address',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => TutorStatusEnum::class,
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

    public function scheduleTutor(): BelongsTo
    {
        return $this->belongsTo(ScheduleTutor::class, 'schedule_tutor_id');
    }
}
