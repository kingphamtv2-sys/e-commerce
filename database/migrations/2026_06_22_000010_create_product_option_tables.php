<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('display_name', 100)->nullable();
            $table->integer('sort_order')->default(0)->index();
            $table->boolean('status')->default(true)->index();
            $table->timestamps();
            $table->unique(['product_id', 'name']);
        });

        Schema::create('product_option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_option_id')->constrained()->cascadeOnDelete();
            $table->string('value', 100);
            $table->string('display_value', 100)->nullable();
            $table->string('color_code', 20)->nullable();
            $table->integer('sort_order')->default(0)->index();
            $table->boolean('status')->default(true)->index();
            $table->timestamps();
            $table->unique(['product_option_id', 'value']);
        });

        Schema::create('product_variant_option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_option_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_option_value_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['product_variant_id', 'product_option_id'], 'variant_one_value_per_option');
            $table->unique(['product_variant_id', 'product_option_value_id'], 'variant_unique_option_value');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_option_values');
        Schema::dropIfExists('product_option_values');
        Schema::dropIfExists('product_options');
    }
};
