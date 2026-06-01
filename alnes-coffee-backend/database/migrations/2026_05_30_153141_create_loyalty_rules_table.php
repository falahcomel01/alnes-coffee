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
    Schema::create('loyalty_rules', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('branch_id')->nullable()->comment('null = berlaku semua cabang');
        $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
        $table->string('name', 150);
        $table->enum('type', ['transaction', 'product', 'tier_bonus', 'birthday'])->default('transaction');
        $table->decimal('earn_per_amount', 12, 2)->default(1000)->comment('Rp per 1 poin');
        $table->decimal('minimum_transaction', 12, 2)->default(0);
        $table->decimal('multiplier', 4, 2)->default(1.00);
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}
};
