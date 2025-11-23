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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->float('score')->default(0);
            $table->integer('score_count')->default(0);
            $table->integer('price')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('source_id')->constrained('sources')->onDelete('cascade');
            $table->string('external_id')->default('');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('url');
            $table->json('row_data')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['source_id', 'external_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
