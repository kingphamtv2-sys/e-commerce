<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_zones', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable()->index();
            $table->text('description')->nullable();
            $table->json('countries')->nullable();
            $table->json('cities')->nullable();
            $table->json('districts')->nullable();
            $table->integer('sort_order')->default(0)->index();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shipping_methods', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shipping_zone_id')->nullable()->constrained('shipping_zones')->nullOnDelete();
            $table->string('code', 80)->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type', 30)->default('flat_rate')->index();
            $table->decimal('base_fee', 15, 2)->default(0);
            $table->decimal('free_shipping_min_amount', 15, 2)->nullable();
            $table->decimal('min_order_amount', 15, 2)->nullable();
            $table->decimal('max_order_amount', 15, 2)->nullable();
            $table->unsignedInteger('estimated_delivery_min_days')->nullable();
            $table->unsignedInteger('estimated_delivery_max_days')->nullable();
            $table->integer('sort_order')->default(0)->index();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['shipping_zone_id', 'status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
        Schema::dropIfExists('shipping_zones');
    }
};
