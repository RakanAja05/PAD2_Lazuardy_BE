<?php

use App\Enums\ClassEnum;
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
        $classes = ClassEnum::list();

        Schema::create('classes', function (Blueprint $table) use ($classes) {
            $table->id();
            $table->enum('name', $classes);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
