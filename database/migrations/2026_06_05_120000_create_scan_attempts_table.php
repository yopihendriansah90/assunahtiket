<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('event_gate_id')->nullable()->constrained('event_gates')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('query')->nullable();
            $table->string('status');
            $table->string('scan_method')->nullable();
            $table->string('student_name')->nullable();
            $table->string('class_name')->nullable();
            $table->string('ticket_code')->nullable();
            $table->timestamp('scanned_at')->useCurrent();
            $table->timestamps();

            $table->index(['event_gate_id', 'scanned_at']);
            $table->index(['status', 'scanned_at']);
            $table->index(['ticket_code', 'scanned_at']);
            $table->index(['student_name', 'scanned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_attempts');
    }
};
