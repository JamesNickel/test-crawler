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
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->integer('score')->default(0);
            $table->integer('score_count')->default(0);
            $table->foreignId('source_id')->constrained('sources')->onDelete('cascade');
            $table->string('external_id')->nullable();
            $table->string('url')->nullable();
            $table->json('row_data')->nullable();
            $table->timestamps();
            $table->softDeletes();
            //$table->unique(['source_id', 'external_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};
