<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('stock')->default(0);
            $table->string('sku', 50)->nullable()->unique();
            $table->boolean('is_best_seller')->default(false);
            $table->boolean('is_special')->default(false);
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_recommended')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
 
            $table->index('category_id');
            $table->index('slug');
            $table->index('is_active');
            $table->index(['is_active', 'is_best_seller']);
            $table->index(['is_active', 'is_popular']);
        });
    }
 
    public function down(): void { Schema::dropIfExists('products'); }
};
