<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_regenerations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason');
            $table->timestamp('regenerated_at')->useCurrent();
            $table->json('old_ticket_snapshot')->nullable();
            $table->json('new_ticket_snapshot')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'regenerated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_regenerations');
    }
};
