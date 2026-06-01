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
    Schema::create('customers', function (Blueprint $table) {
        $table->id();
        $table->string('name', 100);
        $table->string('phone', 20)->unique();
        $table->string('email', 100)->nullable();
        $table->integer('points_balance')->default(0);
        $table->integer('total_points_earned')->default(0);
        $table->enum('tier', ['bronze', 'silver', 'gold', 'platinum'])->default('bronze');
        $table->timestamp('tier_updated_at')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('customers');
}
};
