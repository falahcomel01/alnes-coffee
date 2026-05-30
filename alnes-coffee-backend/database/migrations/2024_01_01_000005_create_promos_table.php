<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration {
    public function up(): void
    {
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('title', 150);
            $table->enum('type', ['percentage', 'fixed'])->default('fixed');
            $table->decimal('value', 12, 2);
            $table->decimal('minimum_purchase', 12, 2)->default(0);
            $table->decimal('maximum_discount', 12, 2)->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
 
            $table->index('code');
            $table->index(['is_active', 'expired_at']);
        });
    }
 
    public function down(): void { Schema::dropIfExists('promos'); }
};