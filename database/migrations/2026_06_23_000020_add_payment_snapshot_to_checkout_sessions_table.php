<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checkout_sessions', function (Blueprint $table): void {
            $table->string('payment_method_code', 50)->nullable()->after('coupon_snapshot');
            $table->string('payment_method_name')->nullable()->after('payment_method_code');
            $table->string('payment_status', 30)->nullable()->after('payment_method_name');
            $table->decimal('payment_amount', 15, 2)->nullable()->after('payment_status');
            $table->string('payment_currency_code', 10)->nullable()->after('payment_amount');
            $table->text('payment_instruction')->nullable()->after('payment_currency_code');
            $table->timestamp('payment_selected_at')->nullable()->after('payment_instruction');
        });
    }

    public function down(): void
    {
        Schema::table('checkout_sessions', function (Blueprint $table): void {
            $table->dropColumn([
                'payment_method_code',
                'payment_method_name',
                'payment_status',
                'payment_amount',
                'payment_currency_code',
                'payment_instruction',
                'payment_selected_at',
            ]);
        });
    }
};
