<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('checkout_session_id')->nullable()->after('user_id')->constrained('checkout_sessions')->nullOnDelete();
            $table->string('success_token', 128)->nullable()->unique()->after('order_code');
            $table->json('contact_snapshot')->nullable()->after('customer_email');
            $table->json('shipping_address_snapshot')->nullable()->after('shipping_address');
            $table->json('billing_address_snapshot')->nullable()->after('shipping_address_snapshot');
            $table->string('currency_symbol', 20)->nullable()->after('currency_code');
            $table->string('currency_symbol_position', 20)->nullable()->after('currency_symbol');
            $table->unsignedTinyInteger('currency_decimal_places')->nullable()->after('currency_symbol_position');
            $table->json('currency_snapshot')->nullable()->after('currency_decimal_places');
            $table->json('tax_snapshot')->nullable()->after('tax_amount');
            $table->json('coupon_snapshot')->nullable()->after('discount_amount');
            $table->string('payment_method_name')->nullable()->after('payment_method');
            $table->text('payment_instruction')->nullable()->after('payment_status');
            $table->timestamp('placed_at')->nullable()->after('cancelled_at');
            $table->unique('checkout_session_id');
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->string('sku', 100)->nullable()->after('variant_name')->index();
            $table->json('option_values_snapshot')->nullable()->after('sku');
            $table->string('image')->nullable()->after('option_values_snapshot');
            $table->string('tax_name')->nullable()->after('subtotal');
            $table->decimal('taxable_amount', 15, 2)->default(0)->after('tax_name');
            $table->json('product_snapshot')->nullable()->after('total');
        });

        Schema::create('order_addresses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20)->index();
            $table->string('full_name');
            $table->string('phone', 30);
            $table->string('country_code', 10)->nullable();
            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('ward')->nullable();
            $table->string('address_line', 500);
            $table->json('raw_snapshot')->nullable();
            $table->timestamps();
        });

        Schema::create('order_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('payment_method_code', 50)->index();
            $table->string('payment_method_name');
            $table->string('payment_status', 50)->default('pending')->index();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('currency_code', 10);
            $table->text('instruction')->nullable();
            $table->timestamp('selected_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('snapshot')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_payments');
        Schema::dropIfExists('order_addresses');

        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropIndex(['sku']);
            $table->dropColumn([
                'sku',
                'option_values_snapshot',
                'image',
                'tax_name',
                'taxable_amount',
                'product_snapshot',
            ]);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropUnique(['checkout_session_id']);
            $table->dropForeign(['checkout_session_id']);
            $table->dropColumn([
                'checkout_session_id',
                'success_token',
                'contact_snapshot',
                'shipping_address_snapshot',
                'billing_address_snapshot',
                'currency_symbol',
                'currency_symbol_position',
                'currency_decimal_places',
                'currency_snapshot',
                'tax_snapshot',
                'coupon_snapshot',
                'payment_method_name',
                'payment_instruction',
                'placed_at',
            ]);
        });
    }
};
