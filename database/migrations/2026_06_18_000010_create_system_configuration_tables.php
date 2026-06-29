<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type', 50)->default('string');
            $table->string('group', 100)->nullable()->index();
            $table->boolean('is_public')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name', 100);
            $table->string('native_name', 100)->nullable();
            $table->boolean('is_default')->default(false)->index();
            $table->boolean('status')->default(true)->index();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name', 100);
            $table->string('symbol', 10);
            $table->decimal('exchange_rate', 15, 6)->default(1);
            $table->unsignedTinyInteger('decimal_places')->default(0);
            $table->string('symbol_position', 20)->default('after');
            $table->string('thousand_separator', 5)->nullable()->default(',');
            $table->string('decimal_separator', 5)->nullable()->default('.');
            $table->boolean('is_default')->default(false)->index();
            $table->boolean('status')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('tax_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 100)->unique();
            $table->text('description')->nullable();
            $table->boolean('status')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_class_id')->constrained()->cascadeOnDelete();
            $table->string('country_code', 10)->nullable()->index();
            $table->string('region', 100)->nullable();
            $table->decimal('rate', 8, 4)->default(0);
            $table->integer('priority')->default(0);
            $table->boolean('status')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
        Schema::dropIfExists('tax_classes');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('languages');
        Schema::dropIfExists('system_settings');
    }
};
