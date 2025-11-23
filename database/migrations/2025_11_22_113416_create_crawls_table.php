<?php

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
        Schema::create('crawls', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->foreignId('source_id')->constrained('sources')->onDelete('cascade');
            $table->integer('start_index')->default(0);
            $table->integer('fetched_count')->default(0);
            $table->enum('status', ['stopped', 'running', 'completed', 'failed'])->default('stopped');
            $table->string('message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crawls');
    }
};
