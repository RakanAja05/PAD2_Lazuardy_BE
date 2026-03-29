<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScheduleTutor extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable =
    [
        'tutor_id',
        'day',
        'time',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_id', 'id');
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class, 'tutor_id', 'user_id');
    }

    public function takenSchedules(): HasMany
    {
        return $this->hasMany(TakenSchedule::class, 'schedule_tutor_id');
    }
}
