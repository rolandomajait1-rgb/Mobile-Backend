<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_verification_analytics', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->enum('event_type', ['sent', 'verified', 'resent', 'expired', 'failed']);
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_verification_analytics');
    }
};
