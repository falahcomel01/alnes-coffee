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
    Schema::create('loyalty_rewards', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('branch_id')->nullable()->comment('null = berlaku semua cabang');
        $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
        $table->string('name', 150);
        $table->text('description')->nullable();
        $table->string('image', 255)->nullable();
        $table->enum('type', ['discount', 'free_item', 'cashback', 'voucher']);
        $table->integer('points_required');
        $table->decimal('value', 12, 2);
        $table->integer('stock')->nullable()->comment('null = unlimited');
        $table->enum('min_tier', ['bronze', 'silver', 'gold', 'platinum'])->nullable();
        $table->timestamp('expired_at')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}
};
