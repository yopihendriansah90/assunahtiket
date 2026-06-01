<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_gate_id')->nullable()->constrained('event_gates')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('scan_method')->default('qr');
            $table->string('scan_value')->nullable();
            $table->timestamp('checked_in_at')->useCurrent();
            $table->timestamps();

            $table->index(['event_id', 'checked_in_at']);
            $table->index(['ticket_id', 'checked_in_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkins');
    }
};
