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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline');
            $table->text('description');
            $table->string('platform');
            $table->string('status')->default('draft');
            $table->unsignedInteger('price')->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('version')->nullable();
            $table->string('requirements')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('demo_url')->nullable();
            $table->string('download_url')->nullable();
            $table->string('repository_url')->nullable();
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('sales')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'platform']);
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
