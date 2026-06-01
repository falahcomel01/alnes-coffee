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
    Schema::create('loyalty_points', function (Blueprint $table) {
        $table->id();
        $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
        $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
        $table->foreignId('rule_id')->nullable()->constrained('loyalty_rules')->nullOnDelete();
        $table->foreignId('adjusted_by')->nullable()->constrained('users')->nullOnDelete();
        $table->enum('type', ['earn', 'redeem', 'expire', 'adjustment']);
        $table->integer('points');
        $table->integer('balance_before');
        $table->integer('balance_after');
        $table->string('description', 255)->nullable();
        $table->timestamp('expired_at')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('loyalty_points');
}
};
