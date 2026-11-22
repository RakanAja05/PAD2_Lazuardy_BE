<?php

use App\Enums\GenderEnum;
use App\Enums\ReligionEnum;
use App\Enums\RoleEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        // membuat data enum
        $roles = RoleEnum::list();
        $genders = GenderEnum::list();
        $religions = ReligionEnum::list();

        Schema::create('users', function (Blueprint $table) use ($roles, $genders, $religions) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->enum('role', $roles)->nullable();
            $table->string('telephone_number', 15)->unique()->nullable();
            $table->timestamp('telephone_verified_at')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', $genders)->nullable();
            $table->enum('religion', $religions)->default(ReligionEnum::NOT_SET->value);
            $table->json('home_address')->nullable();
            $table->string('google_id')->nullable();
            $table->string('facebook_id')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
