<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration {
    public function up(): void
    {
        Schema::create('cafe_tables', function (Blueprint $table) {
            $table->id();
            $table->string('table_number', 20)->unique();
            $table->string('slug', 50)->unique();
            $table->text('qr_code')->nullable();
            $table->enum('status', ['available', 'occupied'])->default('available');
            $table->timestamps();
 
            $table->index('slug');
            $table->index('status');
        });
    }
 
    public function down(): void { Schema::dropIfExists('cafe_tables'); }
};