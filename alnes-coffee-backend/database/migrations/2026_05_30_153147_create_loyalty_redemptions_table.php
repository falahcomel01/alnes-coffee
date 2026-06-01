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
    Schema::create('loyalty_redemptions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
        $table->foreignId('loyalty_reward_id')->constrained('loyalty_rewards')->cascadeOnDelete();
        $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
        $table->integer('points_used');
        $table->enum('status', ['active', 'used', 'expired'])->default('active');
        $table->timestamp('used_at')->nullable();
        $table->timestamp('expired_at')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('loyalty_redemptions');
}
};
