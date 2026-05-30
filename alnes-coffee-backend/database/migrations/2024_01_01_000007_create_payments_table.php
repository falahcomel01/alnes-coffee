<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('payment_gateway', 50)->default('midtrans');
            $table->string('transaction_id', 100)->nullable()->unique();
            $table->string('payment_type', 50)->nullable();
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->json('payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
 
            $table->index('order_id');
            $table->index('transaction_id');
            $table->index('status');
        });
    }
 
    public function down(): void { Schema::dropIfExists('payments'); }
};
