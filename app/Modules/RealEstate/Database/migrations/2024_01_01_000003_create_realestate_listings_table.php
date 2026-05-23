<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('realestate_listings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('type')->index();
            $table->string('status')->default('available')->index();
            $table->decimal('price', 14, 2)->default(0);
            $table->string('currency', 3)->default('TRY');
            $table->unsignedSmallInteger('rooms')->nullable();
            $table->unsignedSmallInteger('bathrooms')->nullable();
            $table->decimal('area_m2', 10, 2)->nullable();
            $table->string('city')->index();
            $table->string('district')->nullable();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->longText('description')->nullable();
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('realestate_listings');
    }
};
