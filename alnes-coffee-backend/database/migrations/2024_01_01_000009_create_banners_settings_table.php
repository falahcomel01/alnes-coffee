<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration {
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->string('image');
            $table->string('link')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
 
            $table->index(['is_active', 'sort_order']);
        });
 
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('cafe_name', 150)->default('Alnes Coffee');
            $table->string('logo')->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('instagram', 100)->nullable();
            $table->string('facebook', 100)->nullable();
            $table->string('tiktok', 100)->nullable();
            $table->text('maps_url')->nullable();
            $table->time('open_time')->default('07:00:00');
            $table->time('close_time')->default('22:00:00');
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->decimal('service_fee', 12, 2)->default(1000);
            $table->boolean('is_open')->default(true);
            $table->timestamps();
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('banners');
    }
};