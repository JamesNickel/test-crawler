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
        Schema::create('comments', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('description');
            $table->integer('score')->default(0);
            $table->boolean('is_buyer')->default(false);
            $table->integer('like_count')->default(0);
            $table->integer('dislike_count')->default(0);
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('seller_id')->nullable()->constrained('sellers')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
