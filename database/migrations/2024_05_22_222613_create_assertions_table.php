<?php

declare(strict_types=1);

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
        Schema::create('assertions', function (Blueprint $table): void {
            $table->id();
            $table->enum('type', ['response.time', 'response.code', 'response.body', 'response.json', 'response.header', 'ssl_certificate.expires_in']);
            $table->enum('sign', ['eq', 'neq', 'gt', 'gte', 'lt', 'lte', 'contains', 'not_contains', 'regex']);
            $table->text('value');
            $table->foreignId('check_id')->constrained()->cascadeOnDelete();
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
