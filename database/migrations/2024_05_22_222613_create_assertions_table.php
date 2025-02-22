<?php

use App\Enums\AssertionSign;
use App\Enums\AssertionType;
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
        Schema::create('assertions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', AssertionType::values());
            $table->enum('sign', AssertionSign::values());
            $table->text('value');
            $table->foreignId('check_id')
                ->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assertions');
    }
};
