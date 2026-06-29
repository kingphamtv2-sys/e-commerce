<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkout_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id')->nullable()->index();
            $table->string('token', 128)->unique();
            $table->string('status', 30)->default('draft')->index();
            $table->string('contact_name');
            $table->string('contact_email');
            $table->string('contact_phone', 30);
            $table->json('shipping_address');
            $table->json('billing_address');
            $table->boolean('billing_same_as_shipping')->default(true);
            $table->json('items_snapshot');
            $table->json('tax_snapshot');
            $table->json('currency_snapshot');
            $table->json('coupon_snapshot')->nullable();
            $table->text('note')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('shipping_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->dateTime('expires_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkout_sessions');
    }
};
