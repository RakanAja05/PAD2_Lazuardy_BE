<?php

namespace App\Models;

use App\Enums\GenderEnum;
use App\Enums\ReligionEnum;
use App\Enums\RoleEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Kolom yang bisa diisi mass assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'role',
        'telephone_number',
        'telephone_verified_at',
        'google_id',
        'facebook_id',
        'profile_photo_path',
        'date_of_birth',
        'gender',
        'religion',
        'home_address',
        'latitude',
        'longitude',
    ];

    /**
     * Kolom yang harus disembunyikan saat serialisasi.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting atribut ke tipe tertentu.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'home_address' => 'array',
            'date_of_birth' => 'date',
            'role' => RoleEnum::class,
            'religion' => ReligionEnum::class,
            'gender' => GenderEnum::class,
            'telephone_verified_at' => 'datetime',
        ];
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function tutor(): HasOne
    {
        return $this->hasOne(Tutor::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ScheduleTutor::class, 'tutor_id', 'id');
    }

    public function takenSchedules(): HasMany
    {
        return $this->hasMany(TakenSchedule::class, 'student_id', 'id');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(
            Subject::class,
            'tutor_subjects',
            'tutor_id',
            'subject_id',
            'id',
            'id'
        );
    }

    public function files(): HasMany {
        return $this->hasMany(File::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviewStudents(): HasMany
    {
        return $this->hasMany(Review::class, 'student_id', 'id');
    }

    public function reviewTutors(): HasMany
    {
        return $this->hasMany(Review::class, 'tutor_id', 'id');
    }

    public function scopeGetUserByEmail($query, $email)
    {
        return $query->where('email', $email)->first();
    }
}
