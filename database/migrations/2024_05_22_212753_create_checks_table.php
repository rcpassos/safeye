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
        Schema::create('checks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('group_id')
                ->nullable()
                ->constrained();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['http']);
            $table->string('endpoint');
            $table->enum('http_method', ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD'])->nullable();
            $table->unsignedBigInteger('interval')->default(60);
            $table->unsignedBigInteger('request_timeout')->default(10);
            $table->json('request_headers')->nullable();
            $table->json('request_body')->nullable();
            $table->text('notify_emails')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checks');
    }
};
