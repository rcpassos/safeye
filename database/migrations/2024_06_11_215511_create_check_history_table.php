<?php

use App\Enums\CheckHistoryType;
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
        Schema::create('check_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('check_id')
                ->constrained();
            $table->text('notified_emails')->nullable();
            $table->json('metadata')->nullable();
            $table->json('root_cause')->nullable();
            $table->enum('type', CheckHistoryType::values());
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_history');
    }
};
