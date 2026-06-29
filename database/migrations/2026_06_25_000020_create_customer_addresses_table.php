<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->string('recipient_name');
            $table->string('phone', 30);
            $table->string('address_line_1', 500);
            $table->string('address_line_2', 500)->nullable();
            $table->string('city');
            $table->string('district')->nullable();
            $table->string('ward')->nullable();
            $table->string('postal_code', 30)->nullable();
            $table->string('country', 10)->default('VN')->index();
            $table->boolean('is_default_shipping')->default(false)->index();
            $table->boolean('is_default_billing')->default(false)->index();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'is_default_shipping']);
            $table->index(['user_id', 'is_default_billing']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
