<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained('cafe_tables')->cascadeOnDelete();
            $table->string('session_token', 100)->unique();
            $table->string('customer_name', 100)->nullable();
            $table->string('customer_phone', 20)->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
 
            $table->index('session_token');
            $table->index('table_id');
        });
    }
 
    public function down(): void { Schema::dropIfExists('customer_sessions'); }
};
