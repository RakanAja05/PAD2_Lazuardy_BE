<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutor_id')->constrained('users', 'id')->cascadeOnDelete();
            $table->string('payout_number')->unique();
            $table->string('xendit_id')->nullable();
            $table->integer('amount');
            $table->string('bank_code');
            $table->string('account_number');
            $table->string('account_holder_name');
            $table->enum('status', ['requested', 'pending', 'success', 'rejected', 'failed'])->default('requested');
            $table->string('failure_code')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('rejected_reason')->nullable();
            $table->json('payload_raw')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
